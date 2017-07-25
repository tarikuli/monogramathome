<?php
/**
 * Julfiker_Contact extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category       Julfiker
 * @package        Julfiker_Contact
 * @copyright      Copyright (c) 2016
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Contact front contrller
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author      Ultimate Module Creator
 */
class Julfiker_Contact_QueueController extends Mage_Core_Controller_Front_Action
{
    const ATTRIBUTE_SET = "AllOpen";
    const TABLERATE_CONDITION_NAME = "package_weight";

    /**
     * init the contact
     *
     * @access protected
     * @return Julfiker_Contact_Model_Ambassadorqueue
     */
    protected function _initAmbassadorqueque() {
        return Mage::getModel('julfiker_contact/ambassadorqueue');
    }

    public function runAction() {

        $queques = Mage::getModel('julfiker_contact/ambassadorqueue')->getCollection()
            ->addFilter('status','0')->load();

        foreach ($queques as $q) {
            $domain = $q->getDomainId();
            try {
                //#addWebsite
                /** @var $website Mage_Core_Model_Website */
                $website = Mage::getModel('core/website');
                $website->setCode(strtolower($domain))
                    ->setName("$domain.monogramathome.com")
                    ->save();

                //#addStoreGroup
                /** @var $storeGroup Mage_Core_Model_Store_Group */
                $storeGroup = Mage::getModel('core/store_group');
                $storeGroup->setWebsiteId($website->getId())
                    ->setName(strtolower($domain)."_store")
                    ->setRootCategoryId(2)
                    ->save();

                //#addStore
                /** @var $store Mage_Core_Model_Store */
                $store = Mage::getModel('core/store');
                $store->setCode(strtolower($domain)."_store_en")
                    ->setWebsiteId($storeGroup->getWebsiteId())
                    ->setGroupId($storeGroup->getId())
                    ->setName("$domain"."_en")
                    ->setIsActive(1)
                    ->save();


                // 1. make ambassador active after their domains has been created.
                // 2. assign ambassador to their website and store, so that they will get logged in to
                //    their website automatically after redirecting from main website to their website.
                $customerId= $q->getCustomerId();
                if($customerId != 0)
                {
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    if($customer->getId())
                    {
                        $customer->setIsActive(1);
                        $customer->setWebsiteId($website->getId());
                        $customer->setStoreId($store->getId());
                        $customer->save();
                    }
                }
            }
            catch(\Exception $e) {
                continue;
            }
        }
        $response = array("status"=> "success", "message"=>"Successfully created store based on domain queue");
        $this->jsonResponse($response);
    }

    /**
     * Assign to all production to multi store
     */
    public function productsAction() {
        try {
            $this->_productAssignToWebsite();
            $response = array("status"=> "success", "message"=>"Successfully assigned all products to created store");
            $this->jsonResponse($response);
        }
        catch (\Exception $e) {
            $response = array("status"=> "failed", "message"=> $e->getMessage());
            $this->jsonResponse($response);
        }
    }

    /**
     * Updating htaccess based on multi store
     */
    public function htaccessAction() {
        try {
            $this->_updatingHtaccess();
            $response = array("status"=> "success", "message"=>"htaccess has been updated successfully with all store view.");
            $this->jsonResponse($response);
        }
        catch (\Exception $e) {
            $response = array("status"=> "failed", "message"=>$e->getMessage());
            $this->jsonResponse($response);
        }
    }

    /**
     * Configure all store with base url
     */
    public function configAction() {
        try {
            $this->_setConfigBaseUrlToStore();
            $response = array("status"=> "success", "message"=>"Configuration has been done with base url based on multi store");
            $this->jsonResponse($response);
        }
        catch (\Exception $e) {
            $response = array("status"=> "failed", "message"=>$e->getMessage());
            $this->jsonResponse($response);
        }
    }

    /**
     * Dynamic clear all cache action
     */
    public function clearCacheAction() {
        try {
            Mage::getConfig()->cleanCache();
            Mage::app()->getCacheInstance()->flush();
            Mage::app()->cleanCache();
            $response = array("status"=> "success", "message"=>"Cache bas been cleared");
            $this->jsonResponse($response);
        }
        catch (\Exception $e) {
            $response = array("status"=> "failed", "message"=>$e->getMessage());
            $this->jsonResponse($response);
        }
    }

    /**
     * Dynamic indexing catalog and attributes
     */
    public function indexingAction() {
        $queques = Mage::getModel('julfiker_contact/ambassadorqueue')->getCollection()
            ->addFilter('status','0')->load();

        if (count($queques) > 0) {
            try {
                /* @var $indexCollection Mage_Index_Model_Resource_Process_Collection */
                $indexCollection = Mage::getModel('index/process')->getCollection();
                foreach ($indexCollection as $index) {
                    /* @var $index Mage_Index_Model_Process */
                    $index->reindexAll();
                }

                foreach ($queques as $q) {
                    $q->setStatus(1)->save();
                }
                $response = array("status"=> "success", "message"=>"Indexing bas been done in dynamic way");
                $this->jsonResponse($response);
            }
            catch(\Exception $e) {
                $response = array("status"=> "failed", "message"=>$e->getMessage());
                $this->jsonResponse($response);
            }
        }
        else {
            $response = array("status"=> "success", "message"=>"No domain in queue, so no need to run indexing");
            $this->jsonResponse($response);
        }
    }

    /**
     * Product assign to all websites
     *
     * return void
     */
    protected function _productAssignToWebsite() {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $website_ids = array();
        $website_collection = Mage::app()->getWebsites(true);
        foreach($website_collection as $website) {
            $website_ids[] = $website->getId();
        }
        $product_collection = Mage::getModel('catalog/product')->getCollection();
        $i = 0;
        foreach($product_collection as $product) {
            /** Adding to queue processing multi store dynamically */
            $product = Mage::getModel('catalog/product')->load($product->getId());
            $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
            $attributeSetModel->load($product->getAttributeSetId());
            $attributeSetName  = $attributeSetModel->getAttributeSetName();
            if(0 != strcmp($attributeSetName, self::ATTRIBUTE_SET)) {
                $product->setWebsiteIds($website_ids);
                $product->save();
                $i++;
            }
        }

        echo "Total $i product items has been updated with all domain or store";
    }

    /**
     * Set configuration to set basedUrl to store specific
     */
    protected function _setConfigBaseUrlToStore() {
        Mage::getConfig()->cleanCache();
        foreach (Mage::app()->getWebsites() as $website) {

            //TableRates automation
            $config = Mage::getModel('core/config');
            $config->saveConfig('carriers/tablerate/import','tablerates.csv','websites',$website->getId());
            $tableRates = $this->_findWebsiteTableRate($website->getId());
            if (!count($tableRates)>0) {
                $defaultRates = $this->_getDefaultTableRates();
                foreach ($defaultRates as $tableRate) {
                    $binds = array(
                        'website_id' => $website->getId(),
                        'condition_value' => $tableRate->getConditionValue(),
                        'price' => $tableRate->getPrice(),
                        'condition_name' => self::TABLERATE_CONDITION_NAME
                    );
                    $this->_insertTableRate($binds);
                }
            }

            if ($website->getCode() == "base") continue;
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $config = Mage::getModel('core/config');
                    $value = "https://".$website->getName()."/";
                    $sValue = "https://".$website->getName()."/";
                    $config->saveConfig('web/unsecure/base_url',$value,'stores',$store->getId());
                    $config->saveConfig('web/secure/base_url',$sValue,'stores',$store->getId());
                }
            }
        }
    }

    /**
     * Updating .htaccess based on multi store setup
     */
    protected function _updatingHtaccess() {
        $block = $this->getLayout()->createBlock('julfiker_contact/htaccess');
        $block->setTemplate('htaccess/htaccess.phtml');
        $content = $block->toHtml();

        $baseDir = Mage::getBaseDir();
        $f = fopen("$baseDir/.htaccess", "w") or $this->throwFailedException();
        fwrite($f, $content);
        fclose($f);

        return true;
    }

    private function throwFailedException() {
        throw new Exception("Failed to open .htaccess file. something went wrong or might be permission denied to open .htaccess file");
    }

    /**
     * Json response
     *
     * @param $response
     */
    protected function jsonResponse($response) {
        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
        $this->getResponse()->setBody(json_encode($response));
    }

    /**
     * Collections of shipping table rates data based on website
     *
     * @param $websiteId
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate_Collection|Object
     */
    protected function _findWebsiteTableRate($websiteId) {
        $tablerateColl = Mage::getResourceModel('shipping/carrier_tablerate_collection');
        /* @var $tablerateColl Mage_Shipping_Model_Resource_Carrier_Tablerate_Collection */
        $tablerateColl->addFieldToFilter("website_id", $websiteId);
        return $tablerateColl;
    }

    /**
     * Add new row into the shipping table rates
     *
     * @param $binds
     * @return bool
     */
    protected function _insertTableRate($binds) {
        try {
            $resource = Mage::getResourceModel('shipping/carrier_tablerate');
            $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
            $query = "insert into {$resource->getMainTable()} (website_id, condition_value, condition_name, price) "
                . "values (:website_id, :condition_value, :condition_name, :price)";
            $adapter->query($query, $binds);
            return true;
        }
        catch (Exception $e) {
            //Exception handling..
            echo $e->getTraceAsString();
        }

        return false;
    }

    /**
     * Get Default shipping table rates data, It must be import tablerates.csv with data under the default config scope
     * Otherwise it won't work. Please check  data is exists in shipping table rates under the default config scope.
     *
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate_Collection|Object
     */
    protected function _getDefaultTableRates() {
        return $this->_findWebsiteTableRate(0);
    }

    public function configInfoAction() {
        foreach (Mage::app()->getWebsites() as $website) {
            if ($website->getCode() == "base") continue;
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $websiteName = str_replace(".com", ".info",$website->getName());
                    $config = Mage::getModel('core/config');
                    $value = "http://".$websiteName."/";
                    $sValue = "http://".$websiteName."/";
                    $config->saveConfig('web/unsecure/base_url',$value,'stores',$store->getId());
                    $config->saveConfig('web/secure/base_url',$sValue,'stores',$store->getId());
                }
            }
        }
    }
}
