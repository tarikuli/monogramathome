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
 * Observer model
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Model_Observer
{
    /**
     * When checkout success done automatically call this method
     *
     * @param $observer
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function setEventOrder($observer) {
        $orderIds = $observer->getOrderIds();
        $eventId = Mage::getSingleton('core/session')->getEventId();
        $iDefaultStoreId = Mage::app()
            ->getWebsite()
            ->getDefaultGroup()
            ->getDefaultStoreId();
        if ($eventId) {
            foreach ($orderIds as $orderId) {
                //$orderObject = Mage::getModel('sales/order')->load($orderId);
                //$orderId = $orderObject->getIncrementId();
                $orderItem = Mage::getModel('julfiker_party/partyorderitem');
                $orderItem->setStatus(1);
                $orderItem->setEventId($eventId);
                $orderItem->setOrderId($orderId);
                $orderItem->setStores(array($iDefaultStoreId));
                $orderItem->save();
            }
        }
    }

    /**
     * Once customer register successfully
     *
     * @param $observer
     */
    public function customerRegisterOrLoginSuccess($observer){
        $urlToContinue = Mage::getSingleton('customer/session')->getBeforeAuthUrl();
        $isContinue = Mage::getSingleton('customer/session')->getContinueToEvent();
        if ($isContinue)
        Mage::getSingleton('customer/session')->addNotice(Mage::helper('julfiker_party')->__('Please continue with event response by <a href="'.$urlToContinue.'">click here</a>'));

        Mage::getSingleton('customer/session')->unsContinueToEvent();
    }
}
