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

    /**
     * init the contact
     *
     * @access protected
     * @return Julfiker_Contact_Model_Ambassadorqueue
     */
    protected function _initAmbassadorqueque()
    {
        return Mage::getModel('julfiker_contact/ambassadorqueue');
    }


    public function runAction() {

       $queques = Mage::getModel('julfiker_contact/ambassadorqueue')->getCollection()->load();

        foreach ($queques as $q) {
            $domain = $q->getDomainId();

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
            die("ki holo");
        }
    }
}
