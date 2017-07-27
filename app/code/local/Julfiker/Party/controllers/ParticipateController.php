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

    /**
     * Event invitation action
     *
     * @throws Exception
     */
    public function inviteAction() {
        $isInvited = false;
        $eventId = $this->getRequest()->get('event_id');
        $members = $this->getRequest()->get('members');
        $emails = $this->getRequest()->get('emails');

        $status = Mage::helper('julfiker_party/event')->getAllEventStatus();

        $customer = $this->_initCustomer();
        $customerId = ($customer->getId())?$customer->getId():0;

        //Sending invitation to existing contacts
        if (count($members)>0) {
            foreach ($members as $key => $val) {
                $participated = Mage::getModel('julfiker_party/partyparticipate')
                    ->getCollection()
                    ->addFieldToFilter('contact_id', $val)
                    ->addFieldToFilter('event_id', $eventId)
                    ->getFirstItem();

                $partycipate = Mage::getModel('julfiker_party/partyparticipate')->load($participated->getId());
                if (!$partycipate->getId()) {
                    $contact = Mage::getSingleton('julfiker_party/contact')->load($val);
                    $partycipate = Mage::getModel('julfiker_party/partyparticipate');
                    $partycipate->setEventId($eventId);
                    $partycipate->setStatus($status['STATUS_INVITE']);
                    $partycipate->setInvitedBy($customerId);
                    $partycipate->setContactId($contact->getId());
                    $partycipate->setInviteEmail($contact->getEmail());
                    $partycipate->save();

                    if ($partycipate->getId()) {
                        $data = Mage::helper("julfiker_party/sender")->getEmailData($eventId);
                        $data['name'] = $contact->getFirstName();
                        $data['joinUrl'] .= "?status=" . $status['STATUS_JOINED'] . "&id=" . $partycipate->getId();
                        $data['rejectUrl'] .= "?status=" . $status['STATUS_INVITE_REJECT'] . "&id=" . $partycipate->getId();
                        Mage::helper("julfiker_party/sender")->sendInviteEmail($contact->getEmail(), $data);
                        $isInvited = true;
                    }
                }
            }
        }

        //Sending invitation to new email
        if (count($emails)>0) {
            foreach ($emails as $key => $val) {
                if ($val) {
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
                        $partycipate->setInvitedBy($customerId);
                        $partycipate->setCustomerId(0);
                        $partycipate->save();

                        if ($partycipate->getId()) {
                            $data = Mage::helper("julfiker_party/sender")->getEmailData($eventId);
                            $data['name'] = "Valued guest";
                            $data['joinUrl'] .= "?status=" . $status['STATUS_JOINED'] . "&id=" . $partycipate->getId();
                            $data['rejectUrl'] .= "?status=" . $status['STATUS_INVITE_REJECT'] . "&id=" . $partycipate->getId();
                            Mage::helper("julfiker_party/sender")->sendInviteEmail($val, $data);
                            $isInvited = true;
                        }
                    }
                }
            }
        }

        $_event = $participate = Mage::getModel('julfiker_party/event')->load($eventId);
        if ($isInvited)
        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('julfiker_party')->__('Invitation has been sent!'));
        $this->_redirectUrl($_event->getEventUrl());
    }

    /**
     * Perform to join functionality
     *
     * @param $eventId
     * @return Zend_Controller_Response_Abstract
     * @throws Exception
     */
    public function going($eventId) {
        $customer = $this->_initCustomer();
        $customerId = ($customer->getId())?$customer->getId():0;
        if ($customerId) {
            $event = Mage::getModel('julfiker_party/event');
            $status = Mage::helper('julfiker_party/event')->getAllEventStatus();
            $event->setStoreId(Mage::app()->getStore()->getId())
                    ->load($eventId);

            $participate = Mage::getModel('julfiker_party/partyparticipate');
            $id = $this->getRequest()->get("id");
//            if ($id)
//                $participate->load($id);

            $guest = $this->getRequest()->get('guest');
            $participate->setEventId($eventId);
            $participate->setStatus($status['STATUS_JOINED']);
            $participate->setGuest($guest);
            $participate->setCustomerId($customerId);
            $participate->save();
            Mage::getSingleton('core/session')->setEventId($eventId);
            Mage::getSingleton('core/session')->setEventConfirmSuccessId($participate->getId());
            Mage::getSingleton('customer/session')->setContinueToEvent(false);
            //Mage::getSingleton('customer/session')->addSuccess(Mage::helper('julfiker_party')->__('Thank you very much for your participation! As you have joined please continue shopping for '.$eventTitle));
            return Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('party/participate/success'));
        }
        else {
            Mage::getSingleton('customer/session')->addError(Mage::helper('julfiker_party')->__('You must be logged in to perform this action!'));
        }
        $this->_redirectReferer();
    }

    /**
     * Functionality to update status with may be or interest
     *
     * @param $eventId
     * @return Zend_Controller_Response_Abstract
     * @throws Exception
     */
    public function interest($eventId) {
        $customer = $this->_initCustomer();
        $customerId = ($customer->getId())?$customer->getId():0;
        if ($customerId) {
            $event = Mage::getModel('julfiker_party/event');
            $status = Mage::helper('julfiker_party/event')->getAllEventStatus();
            $event->setStoreId(Mage::app()->getStore()->getId())
                    ->load($eventId);

            $participate = Mage::getModel('julfiker_party/partyparticipate');
            $id = $this->getRequest()->get("id");
//            if ($id)
//                $participate->load($id);

            $participate->setEventId($eventId);
            $participate->setStatus($status['STATUS_INTERESTED']);
            $participate->setGuest(0);
            $participate->setCustomerId($customerId);
            $participate->save();
            Mage::getSingleton('core/session')->setEventConfirmSuccessId($participate->getId());
            Mage::getSingleton('customer/session')->setContinueToEvent(false);
            //Mage::getSingleton('customer/session')->addSuccess(Mage::helper('julfiker_party')->__('Thank you very much for your participation!'));
            return Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('party/participate/success'));
        }
        else {
            Mage::getSingleton('customer/session')->addError(Mage::helper('julfiker_party')->__('You must be logged in to perform this action!'));
        }
        $this->_redirectReferer();
    }

    /**
     * Functionality to update status with No or Reject
     *
     * @param $eventId
     * @return Zend_Controller_Response_Abstract
     * @throws Exception
     */
    public function reject($eventId) {
        $customer = $this->_initCustomer();
        $customerId = ($customer->getId())?$customer->getId():0;
        if ($customerId) {
            $event = Mage::getModel('julfiker_party/event');
            $status = Mage::helper('julfiker_party/event')->getAllEventStatus();
            $event->setStoreId(Mage::app()->getStore()->getId())
                ->load($eventId);

            $participate = Mage::getModel('julfiker_party/partyparticipate');
            $id = $this->getRequest()->get("id");
//            if ($id)
//                $participate->load($id);

            $participate->setEventId($eventId);
            $participate->setStatus($status['STATUS_INVITE_REJECT']);
            $participate->setGuest(0);
            $participate->setCustomerId($customerId);
            $participate->save();
            Mage::getSingleton('core/session')->setEventConfirmSuccessId($participate->getId());
            Mage::getSingleton('customer/session')->setContinueToEvent(false);
            //Mage::getSingleton('customer/session')->addSuccess(Mage::helper('julfiker_party')->__('Thank you very much for your participation!'));
            return Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('party/participate/success'));
        }
        else {
            Mage::getSingleton('customer/session')->addError(Mage::helper('julfiker_party')->__('You must be logged in to perform this action!'));
        }
        $this->_redirectReferer();
    }

    /**
     * Participation response action for an event
     *
     * @return Zend_Controller_Response_Abstract
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function responseAction() {

        if (!Mage::getSingleton("customer/session")->isLoggedIn()) {
            $currentUrl = Mage::helper('core/url')->getCurrentUrl();
            Mage::getSingleton('customer/session')->setBeforeAuthUrl($currentUrl);
            Mage::getSingleton('customer/session')->setContinueToEvent(true);
            Mage::getSingleton('customer/session')->addNotice(Mage::helper('julfiker_party')->__('Please continue with event response by login or create an account, its free!'));
            return Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('customer/account/login'));
        }

        $id = $this->getRequest()->get('id');
        $status = $this->getRequest()->get('status');
        $eventId = $this->getRequest()->get('event_id');
        $participate = Mage::getModel('julfiker_party/partyparticipate')->load($id);
        $statusConstant = Mage::helper('julfiker_party/event')->getAllEventStatus();
        if ($participate->getId()) {
            $eventId = $participate->getEventId();
        }

        if ($eventId and $statusConstant['STATUS_JOINED'] == $status) {
            $this->going($eventId);
        }
        elseif ($eventId and $statusConstant['STATUS_INTERESTED'] == $status) {
            $this->interest($eventId);
        }
        elseif ($eventId and $statusConstant['STATUS_INVITE_REJECT'] == $status) {
            $this->reject($eventId);
        }else
        $this->_redirectReferer();
    }

    /**
     * Participation confirmation page
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function confirmAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');

        $id = $this->getRequest()->get('id');
        $participate = Mage::getModel('julfiker_party/partyparticipate')->load($id);
        if ($participate->getId()) {
            $_event = Mage::getModel('julfiker_party/event')->load($participate->getEventId());
            Mage::register('current_participate', $participate);
        }
        else if ($eventId = $this->getRequest()->get('event_id')) {
            $_event = Mage::getModel('julfiker_party/event')->load($eventId);
        }
        else {
            return Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('events'));
        }

        Mage::register('current_event', $_event);
        $status = $this->getRequest()->get('status');
        if ($_event && $status) {
            if (Mage::helper('julfiker_party/event')->getUseBreadcrumbs()) {
                if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                    $breadcrumbBlock->addCrumb(
                        'home',
                        array(
                            'label'    => Mage::helper('julfiker_party')->__('Home'),
                            'link'     => Mage::getUrl(),
                        )
                    );
                    $breadcrumbBlock->addCrumb(
                        'confirm',
                        array(
                            'label' => "Confirm participate",
                            'link'  => '',
                        )
                    );
                }
            }
        }

        $this->renderLayout();
    }

    /**
     * Participation success page
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function successAction() {
        $participateId = 0;
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        $participateId = Mage::getSingleton('core/session')->getEventConfirmSuccessId();
        $participate = Mage::getModel('julfiker_party/partyparticipate')->load($participateId);
        if ($participate->getId()) {
            $_event = Mage::getModel('julfiker_party/event')->load($participate->getEventId());
            Mage::register('current_participate', $participate);
            Mage::getSingleton('core/session')->unsEventConfirmSuccessId();
        }
        else {
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('events'));
            return;
        }

        Mage::register('current_event', $_event);
        $status = $this->getRequest()->get('status');
        if ($_event && $status) {
            if (Mage::helper('julfiker_party/event')->getUseBreadcrumbs()) {
                if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                    $breadcrumbBlock->addCrumb(
                        'home',
                        array(
                            'label'    => Mage::helper('julfiker_party')->__('Home'),
                            'link'     => Mage::getUrl(),
                        )
                    );
                    $breadcrumbBlock->addCrumb(
                        'confirm',
                        array(
                            'label' => "Event participate success",
                            'link'  => '',
                        )
                    );
                }
            }
        }
        $this->renderLayout();
    }

}