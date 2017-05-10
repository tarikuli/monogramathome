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
 * Event helper
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Helper_Event extends Mage_Core_Helper_Abstract
{

    const STATUS_INVITE = 1;
    const STATUS_INVITE_REJECT = 2;
    const STATUS_INTERESTED = 3;
    const STATUS_JOINED = 4;

    private $totalParticipates;

    /**
     * get the url to the events list page
     *
     * @access public
     * @return string
     * @author Julfiker
     */
    public function getEventsUrl()
    {
        if ($listKey = Mage::getStoreConfig('julfiker_party/event/url_rewrite_list')) {
            return Mage::getUrl('', array('_direct'=>$listKey));
        }
        return Mage::getUrl('julfiker_party/event/index');
    }

    /**
     * check if breadcrumbs can be used
     *
     * @access public
     * @return bool
     * @author Julfiker
     */
    public function getUseBreadcrumbs()
    {
        return Mage::getStoreConfigFlag('julfiker_party/event/breadcrumbs');
    }

    /**
     * Calculate sum amount of order based on event
     *
     * @param $eventId
     * @return int
     */
    public function sumOrders($eventId) {
        $partyorderitems = Mage::getResourceModel('julfiker_party/partyorderitem_collection')
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('event_id', $eventId);

        $amount = 0;
        foreach ($partyorderitems as $eventOrder) {
            $orderId = $eventOrder->getOrderId();
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            if ($order)
                $amount += $order->getGrandTotal();
        }
        return $amount;
    }

    /**
     * Calculate count invites user based on event
     *
     * @param $eventId
     * @return int
     */
    public function countInvites($eventId) {
        $partyparticipates = $this->getParticipates()
            ->addFieldToFilter('status', self::STATUS_INVITE)
            ->addFieldToFilter('event_id', $eventId);

        return (int)($partyparticipates->getSize() + $this->_countGuest($partyparticipates));
    }

    /**
     * Calculate count already joined user based on event
     *
     * @param $eventId
     * @return int
     */
    public function countJoined($eventId) {
        $partyparticipates = $this->getParticipates()
            ->addFieldToFilter('status', self::STATUS_JOINED)
            ->addFieldToFilter('event_id', $eventId);

        return (int)($partyparticipates->getSize() + $this->_countGuest($partyparticipates));
    }

    /**
     * Calculate count already Interested user based on event
     *
     * @param $eventId
     * @return int
     */
    public function countInterested($eventId) {
        $partyparticipates = $this->getParticipates()
            ->addFieldToFilter('status', self::STATUS_INTERESTED)
            ->addFieldToFilter('event_id', $eventId);

        return (int)($partyparticipates->getSize() + $this->_countGuest($partyparticipates));
    }

    /**
     * Calculate count rejected by user based on event
     *
     * @param $eventId
     * @return int
     */
    public function countInviteRejected($eventId) {
        $partyparticipates = $this->getParticipates()
            ->addFieldToFilter('status', self::STATUS_INVITE_REJECT)
            ->addFieldToFilter('event_id', $eventId);

        return (int)($partyparticipates->getSize() + $this->_countGuest($partyparticipates));
    }

    /**
     * Calculate count total participates based on event
     *
     * @param $eventId
     * @return int
     */
    public function countTotalParticipates($eventId) {

        if (!$this->totalParticipates) {
            $participates = $this->getParticipates()
                ->addFieldToFilter('event_id', $eventId);
            $this->totalParticipates = (int)($participates->getSize()+$this->_countGuest($participates));
        }

        return $this->totalParticipates;

    }

    public function getParticipates() {
        return Mage::getResourceModel('julfiker_party/partyparticipate_collection');
    }

    public function getAllEventStatus() {
        $reflect = new ReflectionClass(get_class($this));
        return $reflect->getConstants();
    }

    private function _countGuest($participates) {
        $guests = 0;
        if ($participates) {
            foreach ($participates as $participate) {
                $guests += (int)$participate->getGuest();
            }
        }
        return $guests;
    }
}
