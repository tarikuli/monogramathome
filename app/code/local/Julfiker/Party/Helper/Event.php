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
    const AMBASSADOR_GROUP_NAME = 'AMBASSADOR';
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
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order)
                $amount += $order->getGrandTotal();
        }
        return $amount;
    }

    /**
     * Count order item based on event
     *
     * @param $eventId
     * @return mixed
     */
    public function countOrders($eventId) {
        $partyOrderItems = Mage::getResourceModel('julfiker_party/partyorderitem_collection')
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('event_id', $eventId);

        return $partyOrderItems->count();
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
     * Checking the customer is joined this event
     * @depreciated
     * @param $eventId
     * @return bool
     */
    public function isCustomerJoinedInEvent($eventId) {
        $customerId = 0;
        $participates = 0;

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customer->getId();
        }

        if ($customerId) {
            $participates = $this->getParticipates()
                ->addFieldToFilter('status', self::STATUS_JOINED)
                ->addFieldToFilter('event_id', $eventId)
                ->addFieldToFilter('customer_id', $customerId)
                ->getSize();
        }
        return ($participates > 0)? true:false;
    }

    /**
     * Checking event is expired or not
     * @param $eventId
     * @return bool
     */
    public function isEventExpired($eventId) {
        $event = Mage::getResourceModel('julfiker_party/event_collection')
            ->addStoreFilter(Mage::app()->getStore())
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('entity_id', $eventId)
            ->addFieldToFilter(
                'end_at',
                array(
                    'gteq' => date ("Y-m-d H:i:s", time())
                ))
            ->getFirstItem();

        if ($event->getId() == $eventId)
            return false;

        return true;
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
        if ($customer->getId())
            return $customer->getName();

         return "Unavailable host";
    }

    public function getAllMembers() {
        $customers = Mage::getResourceModel('customer/customer_collection');

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $customers->addAttributeToFilter('email', array('neq' => $customer->getEmail()));;
        }
        return $customers->addAttributeToFilter('website_id', array('eq' => Mage::app()->getWebsite()->getId()));
    }

    /**
     * Get all contacts based on website
     *
     * @return mixed
     */
    public function getAllContacts() {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $contact = Mage::getModel("julfiker_party/contact");
        return $contact->getCollection()
            ->addFieldToFilter('website_id', $websiteId);
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

        return $members;
    }



    /**
     * Checking permission for ambassador
     *
     * @return bool
     */
    public function checkPermission() {
        $sessionCustomer = Mage::getSingleton("customer/session");
        if($sessionCustomer->isLoggedIn()) {
            $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $group = Mage::getSingleton('customer/group')->load($groupId);
            $groupName = strtoupper($group->getCustomerGroupCode());
            if ($groupName === self::AMBASSADOR_GROUP_NAME) {
                return true;
            }
            else {
                Mage::getSingleton('customer/session')->addError(Mage::helper('julfiker_party')->__('Access denied!'));
                Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('party/event'));
            }
        } else {
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('customer/account/login'));
        }
    }

    /**
     * Checking permission for ambassador to access event page
     * @param $event
     *
     * @return bool
     */
    public function isAmbassador($event) {
        $sessionCustomer = Mage::getSingleton("customer/session");
        if($sessionCustomer->isLoggedIn()) {
            $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $group = Mage::getSingleton('customer/group')->load($groupId);
            $groupName = strtoupper($group->getCustomerGroupCode());
            if ($groupName === self::AMBASSADOR_GROUP_NAME
                && $sessionCustomer->getCustomer()->getId() == $event->getCreatedBy()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checking customer is host for an event
     * @param $event
     * @return bool
     */
    public function isHost($event) {
        $sessionCustomer = Mage::getSingleton("customer/session");
        if($sessionCustomer->isLoggedIn()) {
            if ($sessionCustomer->getCustomer()->getId() == $event->getHost())
                return true;
        }

        return false;
    }

    /**
     * Checking customer has joined this event
     *
     * @param $eventId
     * @return bool
     */
    public function isJoined($eventId) {
        $sessionCustomer = Mage::getSingleton("customer/session");
        $customerId = "";
        if($sessionCustomer->isLoggedIn())
            $customerId = $sessionCustomer->getCustomer()->getId();

        $participated = Mage::getModel('julfiker_party/partyparticipate')
            ->getCollection()
            ->addFieldToFilter('status', self::STATUS_JOINED)
            ->addFieldToFilter('event_id', $eventId)
            ->addFieldToFilter('customer_id', $customerId)
            ->getFirstItem();

        if ($participated->getId())
            return $participated;

        return false;
    }

    /**
     * Event title by host
     *
     * @param $customerId
     * @return string
     */
    public function getEventTitle($customerId) {
        return $this->getHostName($customerId).'\'s '.Mage::helper('julfiker_party')->__("Event");
    }
}
