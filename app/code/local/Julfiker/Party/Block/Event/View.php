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

    /**
     * @return Julfiker_Party_Helper_Event
     */
    public function _eventHelper() {
        return Mage::helper("julfiker_party/event");
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
        return Mage::registry('current_event');
    }

    public function getTotalInvites() {
        $event = $this->getCurrentEvent();
        return $this->_eventHelper()->countInvites($event->getId());
    }

    public function getTotalParticipates() {
        $event = $this->getCurrentEvent();
        return $this->_eventHelper()->countTotalParticipates($event->getId());
    }

    public function getTotalJoined() {
        $event = $this->getCurrentEvent();
        return $this->_eventHelper()->countJoined($event->getId());
    }

    public function getTotalInterested() {
        $event = $this->getCurrentEvent();
        return $this->_eventHelper()->countInterested($event->getId());
    }

    public function getTotalReject() {
        $event = $this->getCurrentEvent();
        return $this->_eventHelper()->countInviteRejected($event->getId());
    }

    public function getTotalOrderAmount() {
        $event = $this->getCurrentEvent();
        return $this->_eventHelper()->sumOrders($event->getId());
    }

    public function getPercentageJoined() {
        $baseTotal = $this->getTotalParticipates();
        $total = $this->getTotalJoined();
        $percent = $this->_percentage($baseTotal, $total);
        return round($percent);
    }

    public function getPercentageInterested() {
        $baseTotal = $this->getTotalParticipates();
        $total = $this->getTotalInterested();
        $percent = $this->_percentage($baseTotal, $total);
        return round($percent);
    }

    public function getPercentageInvites() {
        $baseTotal = $this->getTotalParticipates();
        $total = $this->getTotalInvites();
        $percent = $this->_percentage($baseTotal, $total);
        return round($percent);
    }

    public function getPercentageRejected() {
        $baseTotal = $this->getTotalParticipates();
        $total = $this->getTotalReject();
        $percent = $this->_percentage($baseTotal, $total);
        return round($percent);
    }

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

    private function _percentage($totalAmount, $percentAmount) {
        if ($totalAmount && $percentAmount)
        return ($percentAmount * 100)/$totalAmount;

        return 0;
    }
}
