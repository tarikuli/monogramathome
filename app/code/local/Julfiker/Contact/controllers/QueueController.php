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
            }
            catch(\Exception $e) {
                continue;
            }
        }

        $this->_setConfigBaseUrlToStore();

        echo "successfully created store and configured all store domain specific";
    }

    public function productsAction() {
        $this->_productAssignToWebsite();
    }


    public function htaccessAction() {

        $block = $this->getLayout()->createBlock('julfiker_contact/htaccess');
        $block->setTemplate('htaccess/htaccess.phtml');
        $content = $block->toHtml();

        $baseDir = Mage::getBaseDir();
        $f = fopen("$baseDir/.htaccess", "w") or die("Failed to open .htaccess. something went wrong");
        fwrite($f, $content);
        fclose($f);

        echo "htaccess has been updated successfully with all store view";
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
}