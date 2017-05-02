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
        return "/party/event/save";
    }

    public function getCustomers() {
        $targetGroup = Mage::getModel('customer/group');
        $group = $targetGroup->load('Member', 'customer_group_code');

        $customers = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('*')
            ->addFieldToFilter('website_id', Mage::app()->getStore()->getWebsiteId())
            ->addFieldToFilter('group_id', $group->getId());

        return $customers;
    }
    public function getCustomerAddress($customerId) {
        $customer =  Mage::getModel('customer/customer')->load(171);
        $address = Mage::getModel("Customer/Entity_Address_Collection");
        $address->setCustomerFilter($customer);
        return $address->load();
    }
}
