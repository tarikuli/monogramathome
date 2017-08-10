<?php
/**
 * Julfiker_Party extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Julfiker
 * @package        Julfiker_Party
 * @copyright      Copyright (c) 2017
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Event create block
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author Julfiker
 */
class Julfiker_Party_Block_Event_Event extends Mage_Core_Block_Template
{

    public function getSavingAction() {
        return Mage::getUrl('party/event/save');
    }

    public function getInviteAction() {
        return Mage::getUrl('party/event/invite');
    }

    public function getInterestedAction() {
        return Mage::getUrl('party/event/interested');
    }

    public function getGoingAction() {
        return Mage::getUrl('party/event/going');
    }

    public function getCustomers() {
        $targetGroup = Mage::getModel('customer/group');
        $group = $targetGroup->load('Member', 'customer_group_code');

        $customers = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('*')
            ->addFieldToFilter('website_id', Mage::app()->getStore()->getWebsiteId())
            ->addFieldToFilter('group_id', $group->getId());

        return $customers;
    }


    /**
     * Host customer Id
     *
     * @param $hostCustomerId
     * @return bool
     */
    public function isHostAsMyself($hostCustomerId = 0) {
        if (!$hostCustomerId)
            return false;

        $customerId = 0;
        if(Mage::getSingleton('customer/session')->isLoggedIn())
            $customerId = Mage::getSingleton('customer/session')->getId();

        return $hostCustomerId == $customerId;
    }
}
