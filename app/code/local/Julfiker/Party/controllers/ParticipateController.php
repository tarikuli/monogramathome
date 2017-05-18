<?php

/**
 * Created by PhpStorm.
 * User: julfiker
 * Date: 5/14/17
 * Time: 12:36 AM
 */
class Julfiker_Party_ParticipateController extends Mage_Core_Controller_Front_Action
{
    private function _initCustomer() {
        $customer = Mage::getModel("customer/customer");
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        return $customer;
    }

    public function inviteAction() {
        $eventId = $this->getRequest()->get('event_id');
        $members = $this->getRequest()->get('members');
        $emails = $this->getRequest()->get('emails');

        $status = Mage::helper('julfiker_party/event')->getAllEventStatus();

        $customer = $this->_initCustomer();
        $customerId = ($customer->getId())?$customer->getId():0;

        //Sending invitation to existing members
        foreach ($members as $key => $val) {
            $participated = Mage::getModel('julfiker_party/partyparticipate')
                ->getCollection()
                ->addFieldToFilter('customer_id', $val)
                ->addFieldToFilter('event_id', $eventId)
                ->getFirstItem();

            $partycipate = Mage::getModel('julfiker_party/partyparticipate')->load($participated->getId());
            if (!$partycipate->getId()) {
                $member = Mage::getSingleton('customer/customer')->load($val);
                $partycipate = Mage::getModel('julfiker_party/partyparticipate');
                $partycipate->setEventId($eventId);
                $partycipate->setStatus($status['STATUS_INVITE']);
                $partycipate->setInviteBy($customerId);
                $partycipate->setCustomerId($val);
                $partycipate->save();

                if ($partycipate->getId()) {
                    $data = Mage::helper("julfiker_party/sender")->getEmailData($eventId);
                    $data['name'] = $member->getFirstName();

                    Mage::helper("julfiker_party/sender")->sendInviteEmail($member->getEmail(), $data);
                }
            }
        }

        //Sending invitation to new email
        foreach ($emails as $key => $val) {
            $participated = Mage::getModel('julfiker_party/partyparticipate')
                ->getCollection()
                ->addFieldToFilter('invite_email', $val)
                ->addFieldToFilter('event_id', $eventId)
                ->getFirstItem();

            $partycipate = Mage::getModel('julfiker_party/partyparticipate')->load($participated->getId());
            if (!$partycipate->getId()) {
                $partycipate = Mage::getModel('julfiker_party/partyparticipate');
                $partycipate->setEventId($eventId);
                $partycipate->setInviteEmail($val);
                $partycipate->setStatus($status['STATUS_INVITE']);
                $partycipate->setInviteBy($customerId);
                $partycipate->setCustomerId(0);
                $partycipate->save();

                if ($partycipate->getId()) {
                    $data = Mage::helper("julfiker_party/sender")->getEmailData($eventId);
                    $data['name'] = "Valued guest";
                    Mage::helper("julfiker_party/sender")->sendInviteEmail($val, $data);
                }
            }
        }

        $this->_redirectReferer();
    }


    public function goingAction() {
        $customer = $this->_initCustomer();
        $customerId = ($customer->getId())?$customer->getId():0;
        if ($customerId) {
            $eventId = $this->getRequest()->get('event_id');
            $event = Mage::getModel('julfiker_party/event');
            $status = Mage::helper('julfiker_party/event')->getAllEventStatus();
            if ($eventId) {
                $event->setStoreId(Mage::app()->getStore()->getId())
                    ->load($eventId);
            }
            $partycipate = Mage::getModel('julfiker_party/partyparticipate');
            $id = $this->getRequest()->get("id");
            if ($id)
                $partycipate->load($id);

            $guest = $this->getRequest()->get('guest');
            $partycipate->setEventId($eventId);
            $partycipate->setStatus($status['STATUS_JOINED']);
            $partycipate->setGuest($guest);
            $partycipate->setCustomerId($customerId);
            $partycipate->save();
        }
        else {
            Mage::getSingleton('customer/session')->addError(Mage::helper('julfiker_party')->__('You must be logged in to perform this action!'));
        }
        $this->_redirectReferer();
    }

    public function interestedAction() {
        $customer = $this->_initCustomer();
        $customerId = ($customer->getId())?$customer->getId():0;
        if ($customerId) {
            $eventId = $this->getRequest()->get('event_id');
            $event = Mage::getModel('julfiker_party/event');
            $status = Mage::helper('julfiker_party/event')->getAllEventStatus();
            if ($eventId) {
                $event->setStoreId(Mage::app()->getStore()->getId())
                    ->load($eventId);
            }
            $partycipate = Mage::getModel('julfiker_party/partyparticipate');
            $id = $this->getRequest()->get("id");
            if ($id)
                $partycipate->load($id);

            $partycipate->setEventId($eventId);
            $partycipate->setStatus($status['STATUS_INTERESTED']);
            $partycipate->setGuest(0);
            $partycipate->setCustomerId($customerId);
            $partycipate->save();
        }
        else {
            Mage::getSingleton('customer/session')->addError(Mage::helper('julfiker_party')->__('You must be logged in to perform this action!'));
        }
        $this->_redirectReferer();
    }
}