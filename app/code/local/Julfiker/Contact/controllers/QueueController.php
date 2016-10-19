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
    const ATTRIBUTE_SET = "Kit";

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

       $queques = Mage::getModel('julfiker_contact/ambassadorqueue')->getCollection()->load();

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
             $q->setStatus(1)->save();

            $websiteId = $website->getId();
            $customerId = $q->getCustomerId();
            $customer =  Mage::getModel('customer/customer')->load($customerId);

            //Update customer with related website
            $customer->setWebsiteId($websiteId)
                ->setId($customerId)
                ->setStore($store)
                ->setIsActive(1)
                ->save();

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
        try {
            /* @var $indexCollection Mage_Index_Model_Resource_Process_Collection */
            $indexCollection = Mage::getModel('index/process')->getCollection();
            foreach ($indexCollection as $index) {
                /* @var $index Mage_Index_Model_Process */
                $index->reindexAll();
            }
            $response = array("status"=> "success", "message"=>"Indexing bas been done in dynamic way");
            $this->jsonResponse($response);
        }
        catch(\Exception $e) {
            $response = array("status"=> "failed", "message"=>$e->getMessage());
            $this->jsonResponse($response);
        }
    }

    /**
     * Product assign to all websites
     *
     * return void
     */
    protected function _productAssignToWebsite() {
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
            if ($website->getCode() == "base") continue;
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $config = Mage::getModel('core/config');
                    $value = "http://".$website->getName()."/";
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
}
