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
 * Event view block
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Block_Event_View extends Mage_Core_Block_Template
{

    private $_eventHelper;
    /**
     * @return \Julfiker_Party_Helper_Event
     */
    public function _eventHelper() {
        if (!$this->_eventHelper)
            $this->_eventHelper = Mage::helper("julfiker_party/event");

        return $this->_eventHelper;
    }
    /**
     * get the current event
     *
     * @access public
     * @return mixed (Julfiker_Party_Model_Event|null)
     * @author Julfiker
     */
    public function getCurrentEvent()
    {
        $event = Mage::registry('current_event');
        if ($event)
        $this->_eventHelper()->sumOrders($event->getId());

        return $event;
    }

    /**
     * Host earning amount under an event
     *
     * @return float
     */
    public function getEarningAmount() {
        return $this->_eventHelper()->getEvenEarningAmount();
    }

    /**
     * Host sales amount under an event
     *
     * @return float
     */
    public function getSalesAmount() {
        return $this->_eventHelper()->getEventSalesAmount();
    }

    /**
     * Host earning percent
     *
     * @return float
     */
    public function getEarningPercent() {
        return $this->_eventHelper()->getEventEarningPercent();
    }

    /**
     * Get current participate
     *
     * @return mixed
     */
    public function getCurrentParticipate() {
        return Mage::registry('current_participate');
    }

    /**
     * Get request status
     *
     * @return mixed
     */
    public function getRequestStatus() {
        return Mage::registry('request_status');
    }

    /**
     * @return int
     */
    public function getTotalInvites() {
        $event = $this->getCurrentEvent();
        return $this->_eventHelper()->countInvites($event->getId());
    }

    /**
     * @return int
     */
    public function getTotalParticipates() {
        $event = $this->getCurrentEvent();
        return $this->_eventHelper()->countTotalParticipates($event->getId());
    }

    /**
     * @return int
     */
    public function getTotalJoined() {
        $event = $this->getCurrentEvent();
        return $this->_eventHelper()->countJoined($event->getId());
    }

    /**
     * @return int
     */
    public function getTotalInterested() {
        $event = $this->getCurrentEvent();
        return $this->_eventHelper()->countInterested($event->getId());
    }

    /**
     * @return int
     */
    public function getTotalReject() {
        $event = $this->getCurrentEvent();
        return $this->_eventHelper()->countInviteRejected($event->getId());
    }

    /**
     * @return int
     */
    public function getTotalOrderAmount() {
        $event = $this->getCurrentEvent();
        return $this->_eventHelper()->sumOrders($event->getId());
    }

    /**
     * @return mixed
     */
    public function getTotalOrders() {
        $event = $this->getCurrentEvent();
        return $this->_eventHelper()->countOrders($event->getId());
    }

    /**
     * @return float
     */
    public function getPercentageTotalOrders() {
        $baseTotal = 13;
        $total = $this->getTotalOrders();
        $percent = $this->_percentage($baseTotal, $total);
        return round($percent);
    }

    /**
     * @return float
     */
    public function getPercentageJoined() {
        $baseTotal = 10;
        $total = $this->getTotalJoined();
        $percent = $this->_percentage($baseTotal, $total);
        return round($percent);
    }

    /**
     * @return float
     */
    public function getPercentageInterested() {
        $baseTotal = $this->getTotalParticipates();
        $total = $this->getTotalInterested();
        $percent = $this->_percentage($baseTotal, $total);
        return round($percent);
    }

    /**
     * @return float
     */
    public function getPercentageInvites() {
        $baseTotal = 40;
        $total = $this->getTotalInvites();
        $percent = $this->_percentage($baseTotal, $total);
        return round($percent);
    }

    /**
     * @return float
     */
    public function getPercentageRejected() {
        $baseTotal = $this->getTotalParticipates();
        $total = $this->getTotalReject();
        $percent = $this->_percentage($baseTotal, $total);
        return round($percent);
    }

    /**
     * @return mixed
     */
    public function getCustomers() {
        return Mage::helper("julfiker_party/event")->getAllMembers();
    }

    /**
     * Get all contacts
     *
     * @return mixed
     */
    public function getContacts() {
        return Mage::helper("julfiker_party/event")->getAllContacts();
    }

    /**
     * @param $totalAmount
     * @param $percentAmount
     * @return float|int
     */
    private function _percentage($totalAmount, $percentAmount) {
        if ($totalAmount && $percentAmount)
        return ($percentAmount * 100)/$totalAmount;

        return 0;
    }

    /**
     * Response url
     *
     * @param $status
     * @return string
     */
    public function responseUrl($status) {
        $params = array("status" => $status);
        if ($participate = $this->getCurrentParticipate()) {
            $params['id'] = $participate->getId();
        }
        if ($event = $this->getCurrentEvent()) {
            $params['event_id'] = $event->getId();
        }
       return Mage::getUrl("party/participate/response", array("_query" => $params));
    }
}
