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
 * Event front controller
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_EventController extends Mage_Core_Controller_Front_Action
{
    const AMABASSADOR_GROUP_NAME = 'AMBASSADOR';

    /**
      * default action
      *
      * @access public
      * @return void
      * @author Julfiker
      */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        if (Mage::helper('julfiker_party/event')->getUseBreadcrumbs()) {
            if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbBlock->addCrumb(
                    'home',
                    array(
                        'label' => Mage::helper('julfiker_party')->__('Home'),
                        'link'  => Mage::getUrl(),
                    )
                );
                $breadcrumbBlock->addCrumb(
                    'events',
                    array(
                        'label' => Mage::helper('julfiker_party')->__('View all events'),
                        'link'  => '',
                    )
                );
            }
        }
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->addLinkRel('canonical', Mage::helper('julfiker_party/event')->getEventsUrl());
        }
        $this->renderLayout();
    }

    /**
     * init Event
     *
     * @access protected
     * @return Julfiker_Party_Model_Event
     * @author Julfiker
     */
    protected function _initEvent()
    {
        $eventId   = $this->getRequest()->getParam('id', 0);
        $event     = Mage::getModel('julfiker_party/event');

        if ($eventId) {
            $event->setStoreId(Mage::app()->getStore()->getId())
                ->load($eventId);
        }
        return $event;
    }

    /**
     * view event action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function viewAction()
    {
        $event = $this->_initEvent();
        if (!$event) {
            $this->_forward('no-route');
            return;
        }
        Mage::register('current_event', $event);
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        if ($root = $this->getLayout()->getBlock('root')) {
            $root->addBodyClass('party-event party-event' . $event->getId());
        }
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
                    'events',
                    array(
                        'label' => Mage::helper('julfiker_party')->__('View all events'),
                        'link'  => Mage::helper('julfiker_party/event')->getEventsUrl(),
                    )
                );
                $breadcrumbBlock->addCrumb(
                    'event',
                    array(
                        'label' => $event->getTitle(),
                        'link'  => '',
                    )
                );
            }
        }
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->addLinkRel('canonical', $event->getEventUrl());
        }
        $this->renderLayout();
    }

    public function createAction() {
        if ($this->_checkPermission()) {
            $this->loadLayout();
            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('checkout/session');
            if (Mage::helper('julfiker_party/event')->getUseBreadcrumbs()) {
                if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                    $breadcrumbBlock->addCrumb(
                        'home',
                        array(
                            'label' => Mage::helper('julfiker_party')->__('Home'),
                            'link' => Mage::getUrl(),
                        )
                    );
                    $breadcrumbBlock->addCrumb(
                        'events',
                        array(
                            'label' => Mage::helper('julfiker_party')->__('Book an event'),
                            'link' => '',
                        )
                    );
                }
            }
            $headBlock = $this->getLayout()->getBlock('head');
            if ($headBlock) {
                $headBlock->addLinkRel('canonical', Mage::helper('julfiker_party/event')->getEventsUrl());
            }
            $this->renderLayout();
        }
    }

    public function addressAction() {
        $customerId = $this->getRequest()->get("id");
        if(!$customerId) {
            $customerObj = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customerObj->getId();
        }
        $customer =  Mage::getModel('customer/customer')->load($customerId);
        $data = array();
        foreach ($customer->getAddresses() as $address) {
            $street = $address->getStreet();
            $data[$address->getId()] = $street[0].", ".$address->getCity().", ".$address->getPostcode().", ".$address->getCountry();
        }
        $this->getResponse()->clearHeaders()->setHeader(
            'Content-type',
            'application/json'
        );
        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($data)
        );
    }

    public function saveAction()
    {
        if ($this->_checkPermission()) {
            //$request = $this->getRequest()->get('event');
            $iDefaultStoreId = Mage::app()
                ->getWebsite()
                ->getDefaultGroup()
                ->getDefaultStoreId();

            if ($data = $this->getRequest()->getPost('event')) {
                $data['stores'][] = $iDefaultStoreId;
                $data['start_at'] = $data['start_at'] . " " . $data['start_time'];
                $data['end_at'] = $data['end_at'] . " " . $data['end_time'];

                try {
                    unset($data['start_time']);
                    unset($data['end_time']);
                    $data = $this->_setData($data);
                    $data = $this->_filterDateTime($data, array('start_at', 'end_at'));
                    $event = $this->_initEvent();
                    $event->addData($data);
                    $event->save();
                    $this->_redirect('*/*/');
                    return;
                } catch (Mage_Core_Exception $e) {
                    Mage::getSingleton('fronted/session')->addError($e->getMessage());
                    Mage::getSingleton('frontend/session')->setEventData($data);
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                } catch (Exception $e) {
                    Mage::logException($e);
                    Mage::getSingleton('frontend/session')->addError(
                        Mage::helper('julfiker_party')->__('There was a problem saving the event.')
                    );
                    Mage::getSingleton('frontend/session')->setEventData($data);
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }
            }
            Mage::getSingleton('frontend/session')->addError(
                Mage::helper('julfiker_party')->__('Unable to find event to save.')
            );
            $this->_redirect('*/*/');
        }
    }

//    public function inviteAction() {
//        $eventId = $this->getRequest()->get('event_id');
//        $email = $this->getRequest()->get('email');
//        $customerId = 0;
//        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
//            $customerData = Mage::getSingleton('customer/session')->getCustomer();
//            $customerId = $customerData->getId();
//        }
//
//        $partycipate   = Mage::getModel('julfiker_party/partyparticipate');
//        $partycipate->setEventId($eventId);
//        $partycipate->setStatus(1);
//        //$partycipate->setInviteBy($customerId);
//        $partycipate->setCustomerId(0);
//        $partycipate->save();
//
//        $this->_redirect('*/*/');
//
////        $content =
////        "Hi,
////         Congratulations! You have invited to join an event.
////          yes, I want to join <a href='
////        ";
////
////        $subject = "Monogram invite notification!";
////        $toMail = Mage::getStoreConfig('trans_email/ident_support/email');
////        $mail = Mage::getModel('core/email');
////        $mail->setToName('Event Notification');
////        $mail->setToEmail($email);
////        $mail->setBody($content);
////        $mail->setSubject($subject);
////        $mail->setFromEmail('no-reply@monogramathome.com');
////        $mail->setFromName("Auto Notification");
////        $mail->setType('text');
////
////        try {
////            $mail->send();
////        }
////        catch (Exception $e) {
////            //Todo: add log with exception
////            //die($e->getMessage());
////            echo "<pre>"; var_dump($toMail); echo "</pre>"; die();
////            Mage::log($e->getMessage());
////            die("To: ".$toMail."<br>Subject: ".$subject."<br>Error Message: ".$e->getMessage());
////        }
//    }
//
//    public function goingAction() {
//        $eventId = $this->getRequest()->get('event_id');
//        $event     = Mage::getModel('julfiker_party/event');
//        if ($eventId) {
//            $event->setStoreId(Mage::app()->getStore()->getId())
//                ->load($eventId);
//        }
//        $guest = $this->getRequest()->get('guest');
//
//        $customerId = 0;
//
//        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
//            $customerData = Mage::getSingleton('customer/session')->getCustomer();
//            $customerId = $customerData->getId();
//        }
//        else {
//            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('customer/account/login'));
//        }
//
//        $partycipate   = Mage::getModel('julfiker_party/partyparticipate');
//        $partycipate->setEventId($eventId);
//        $partycipate->setStatus(4);
//        $partycipate->setGuest($guest);
//        $partycipate->setCustomerId($customerId);
//        $partycipate->save();
//        $this->_redirectReferer();
//    }
//
//    public function interestedAction() {
//        $eventId = $this->getRequest()->get('event_id');
//        $event     = Mage::getModel('julfiker_party/event');
//        if ($eventId) {
//            $event->setStoreId(Mage::app()->getStore()->getId())
//                ->load($eventId);
//        }
//        $customerId = 0;
//        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
//            $customerData = Mage::getSingleton('customer/session')->getCustomer();
//            $customerId = $customerData->getId();
//        }
//        else {
//            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('customer/account/login'));
//        }
//
//        $partycipate   = Mage::getModel('julfiker_party/partyparticipate');
//        $partycipate->setEventId($eventId);
//        $partycipate->setStatus(3);
//        $partycipate->setGuest(0);
//        $partycipate->setCustomerId($customerId);
//        $partycipate->save();
//        $this->_redirectReferer();
//    }

    public function orderAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');

        $postData = $this->getRequest()->getPost();

        if ($postData) {
            $eventId = $postData['event'];
            //Todo: check customer has joined this event and also time
            Mage::getSingleton('core/session')->setEventId($eventId);
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('shop.html'));
        }

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
                    'events',
                    array(
                        'label' => Mage::helper('julfiker_party')->__('View all events'),
                        'link'  => Mage::helper('julfiker_party/event')->getEventsUrl(),
                    )
                );
                $breadcrumbBlock->addCrumb(
                    'event',
                    array(
                        'label' => "Make an order",
                        'link'  => '',
                    )
                );
            }
        }
        $this->renderLayout();
    }

    private function _checkPermission() {
        $sessionCustomer = Mage::getSingleton("customer/session");
        if($sessionCustomer->isLoggedIn()) {
            $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $group = Mage::getSingleton('customer/group')->load($groupId);
            $groupName = strtoupper($group->getCustomerGroupCode());
            if ($groupName === self::AMABASSADOR_GROUP_NAME) {
                return true;
            }
            else {
                Mage::getSingleton('customer/session')->addError(Mage::helper('julfiker_party')->__('Please contact ambassador to create an event for you, Only Ambassador can create an event!'));
                Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('party/event'));
            }
        } else {
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('customer/account/login'));
        }
    }

    private function _setData($data) {
        $customerId = 0;
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customerData->getId();
        }
        $data['created_by'] = $customerId;

        if ($data['host'] == "self") {
            $data['host'] = $customerId;
        }
        else if ($data['host'] == "member") {
            $data['host'] = $data['member'];
        }

        if ($data['loc_type'] == "default") {
            $customer = Mage::getSingleton('customer/customer')->load($data['host']);
            $address = $customer->getPrimaryBillingAddress();
            if (!$address) {
                Mage::getSingleton('customer/session')->addError(Mage::helper('julfiker_party')->__("Member doesn't have any default billing address. Please choose location"));
            }
        }
        else if ($data['loc_type'] == "diff") {
            $address = Mage::getModel('customer/address')->load($data['addressId']);
        }
        $data = $this->_setLocation($data, $address);
        $data['status'] = 1;
        $data['url_key'] = strtolower(str_replace(" ", "_",$data['title']));
        return $data;
    }

    private function _setLocation($data, $address) {
        $street = $address->getStreet();
        $data['city'] = $address->getCity();
        $data['zip'] = $address->getPostcode();
        $data['country'] = $address->getCountry();
        $data['address'] = $street[0];
        if (isset($street[1])) {
            $data['address'] .= " ". $street[1];
        }
        return $data;
    }
}
