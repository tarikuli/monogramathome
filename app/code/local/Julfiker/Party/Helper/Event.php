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
    const GROUP_MEMBER_NAME = "Member";

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
        $participates = $this->getParticipates()
            ->addFieldToFilter('status', self::STATUS_INVITE)
            ->addFieldToFilter('event_id', $eventId);

        return (int)($participates->getSize() + $this->_countGuest($participates));
    }

    /**
     * Calculate count already joined user based on event
     *
     * @param $eventId
     * @return int
     */
    public function countJoined($eventId) {
        $participates = $this->getParticipates()
            ->addFieldToFilter('status', self::STATUS_JOINED)
            ->addFieldToFilter('event_id', $eventId);

        return (int)($participates->getSize() + $this->_countGuest($participates));
    }

    /**
     * Calculate count already Interested user based on event
     *
     * @param $eventId
     * @return int
     */
    public function countInterested($eventId) {
        $participates = $this->getParticipates()
            ->addFieldToFilter('status', self::STATUS_INTERESTED)
            ->addFieldToFilter('event_id', $eventId);

        return (int)($participates->getSize() + $this->_countGuest($participates));
    }

    /**
     * Calculate count rejected by user based on event
     *
     * @param $eventId
     * @return int
     */
    public function countInviteRejected($eventId) {
        $participates = $this->getParticipates()
            ->addFieldToFilter('status', self::STATUS_INVITE_REJECT)
            ->addFieldToFilter('event_id', $eventId);

        return (int)($participates->getSize() + $this->_countGuest($participates));
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

    public function getHostName($customerId) {
        $customer = Mage::getSingleton('customer/customer')->load($customerId);
        if ($customer)
            return $customer->getName();

         return null;
    }

    public function getAllMembers() {
        $customers = Mage::getResourceModel('customer/customer_collection');

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $customers->addAttributeToFilter('email', array('neq' => $customer->getEmail()));;
        }
        return $customers;
    }

    /**
     * Get members who got invited for the event
     *
     * @param $eventId
     * @return array
     */
    public function getInvitedMembers($eventId) {
        $participates = $this->getParticipates()
            ->addFieldToFilter('status', self::STATUS_INVITE)
            ->addFieldToFilter('event_id', $eventId);
        return $this->getMemberFromParticipates($participates);
    }

    /**
     * Get members who joined invite for an event
     *
     * @param $eventId
     * @return array
     */
    public function getJoinedMembers($eventId) {
        $participates = $this->getParticipates()
            ->addFieldToFilter('status', self::STATUS_JOINED)
            ->addFieldToFilter('event_id', $eventId);
        return $this->getMemberFromParticipates($participates);
    }

    /**
     * Get members who interested for the event
     *
     * @param $eventId
     * @return array
     */
    public function getInterestedMembers($eventId) {
        $participates = $this->getParticipates()
            ->addFieldToFilter('status', self::STATUS_INTERESTED)
            ->addFieldToFilter('event_id', $eventId);
        return $this->getMemberFromParticipates($participates);
    }

    /**
     * Get members who rejected invite for an event
     *
     * @param $eventId
     * @return array
     */
    public function getRejectMembers($eventId) {
        $participates = $this->getParticipates()
            ->addFieldToFilter('status', self::STATUS_INVITE_REJECT)
            ->addFieldToFilter('event_id', $eventId);
        return $this->getMemberFromParticipates($participates);
    }

    /**
     * Get customer as member based on participate collection
     *
     * @param $participates
     * @return array
     */
    public function getMemberFromParticipates($participates) {
        $members = array();
        foreach ($participates as $participate) {
            $customerId = $participate->getCustomerId();
            $invitedByCustomer = $participate->getInvitedBy();

            $inviteBy = "Guest";
            if ($invitedByCustomer) {
               $cus = Mage::getModel('customer/customer')->load($invitedByCustomer);
               $inviteBy = $cus->getName();
            }

            if ($customerId) {
                $customer = Mage::getModel('customer/customer')->load($customerId);
                $members[] = array(
                    "email" => $customer->getEmail(),
                    "name" => $customer->getName(),
                    "pid" => $participate->getId(),
                    'invitedBy' => $inviteBy,
                    'createdAt' => date("d.m.Y h:i A", strtotime($participate->getUpdatedAt()))
                );
            }
            else {
                $members[] = array(
                    "email" => $participate->getInviteEmail(),
                    "name" => "Guest",
                    "pid" => $participate->getId(),
                    'invitedBy' => $inviteBy,
                    'createdAt' => date("d.m.Y h:i A", strtotime($participate->getUpdatedAt()))
                );
            }
        }

        return json_encode($members);
    }
}