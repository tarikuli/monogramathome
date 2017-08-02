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
        $sessionCustomer = Mage::getSingleton("customer/session");
        if(!$sessionCustomer->isLoggedIn()) {
            Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('party/event'));
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('customer/account/login'));
            return;
        }

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
            return Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('events'));
            return;
        }

        $evenHelper = Mage::helper("julfiker_party/event");
        if ($evenHelper->isAmbassador() || $evenHelper->isHost($event)) {
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
                            'label' => Mage::helper('julfiker_party')->__('Home'),
                            'link' => Mage::getUrl(),
                        )
                    );
                    $breadcrumbBlock->addCrumb(
                        'events',
                        array(
                            'label' => Mage::helper('julfiker_party')->__('View all events'),
                            'link' => Mage::helper('julfiker_party/event')->getEventsUrl(),
                        )
                    );
                    $breadcrumbBlock->addCrumb(
                        'event',
                        array(
                            'label' => $event->getTitle(),
                            'link' => '',
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
        else {
            return Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('events'));
            return;
        }
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
            $this->getLayout()->getBlock('head')->setTitle($this->__('Book an event'));

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

    /**
     * Perform to join functionality
     *
     * @param $eventId
     * @param $customerId
     * @return Zend_Controller_Response_Abstract
     * @throws Exception
     */
    public function going($eventId, $customerId) {
        $status = Mage::helper('julfiker_party/event')->getAllEventStatus();
        $participate = Mage::getModel('julfiker_party/partyparticipate');
        $guest = 0;
        $participate->setEventId($eventId);
        $participate->setStatus($status['STATUS_JOINED']);
        $participate->setGuest($guest);
        $participate->setCustomerId($customerId);
        $participate->save();;
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

                    //Auto participate
                    if ($event->getCreatedBy() == $event->getHost()) {
                        $this->going($event->getId(), $event->getCreatedBy());
                    }
                    else {
                        $this->going($event->getId(), $event->getCreatedBy());
                        $this->going($event->getId(), $event->getHost());
                    }

                    //Welcome email to host
                    if ($event->getHost() != $event->getCreatedBy())
                    Mage::helper("julfiker_party/sender")->sendHostWelcomeEmail($event->getId());

                    Mage::getSingleton('customer/session')->addSuccess(Mage::helper('julfiker_party')->__('Event was created successfully!'));
                    $this->_redirect('*/*/');
                    return;
                } catch (Mage_Core_Exception $e) {
                    Mage::getSingleton('customer/session')->addError($e->getMessage());
                    Mage::getSingleton('customer/session')->setEventData($data);
                    $this->_redirect('*/*/create', array('id' => $this->getRequest()->getParam('id')));
                    return;
                } catch (Exception $e) {
                    Mage::logException($e);
                    Mage::getSingleton('customer/session')->addError(
                        Mage::helper('julfiker_party')->__('There was a problem saving the event.')
                    );
                    Mage::getSingleton('customer/session')->setEventData($data);
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }
            }
            Mage::getSingleton('customer/session')->addError(
                Mage::helper('julfiker_party')->__('Unable to find event to save.')
            );
            $this->_redirect('*/*/');
        }
    }

    /**
     * Delete action of event
     *
     * @throws Exception
     */
    public function deleteAction() {
        $eventId = $this->getRequest()->get('id');
        $event = Mage::getModel("julfiker_party/event")->load($eventId);

        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customerData->getId();
        }

        if ($event->getId() && $event->getCreatedBy() == $customerId) {
            $event->delete();

            //Removing all participation logs for this event
            $participates = Mage::getResourceModel('julfiker_party/partyparticipate_collection')
                ->addFieldToFilter('event_id', $eventId);
            foreach ($participates as $participate) {
                $participate->delete();
            }

            //Remove all order items log for this event
            $oderItems = Mage::getResourceModel('julfiker_party/partyorderitem_collection')
                ->addFieldToFilter('event_id', $eventId);
            foreach ($oderItems as $order) {
                $order->delete();
            }
            Mage::getSingleton('customer/session')->addSuccess(Mage::helper('julfiker_party')->__('Event was deleted successfully!'));
            return $this->_redirect('*/*/index');
        } else {
            Mage::getSingleton('customer/session')->addError(Mage::helper('julfiker_party')->__('Invalid request!'));
        }
        $this->_redirectReferer();
    }

    /**
     * Place an order action
     */
    public function placeOderAction() {
        $eventId = $this->getRequest()->get('id');
        $now = Mage::getModel('core/date')->timestamp(time());
        $event = Mage::getResourceModel('julfiker_party/event_collection')
            ->addStoreFilter(Mage::app()->getStore())
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('entity_id', $eventId)
            ->addFieldToFilter(
                'end_at',
                array(
                    'gteq' => date ("Y-m-d H:i:s", $now)
                ))
            ->getFirstItem();

        if ($event && $event->getId()) {
            $isJoined = Mage::helper("julfiker_party/event")->isJoined($event->getId());
            if ($isJoined) {
                Mage::getSingleton('core/session')->setEventId($eventId);
                Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('shop.html'));
            }
            else {
                Mage::getSingleton('customer/session')->addError(Mage::helper('julfiker_party')->__('You must be joined in this event, then you can place an order!'));
                return $this->_redirectReferer();
            }
        }
        else {
            Mage::getSingleton('customer/session')->addError(Mage::helper('julfiker_party')->__('Oops this event is over BUT you can still take advantage of the great items by shopping now. Please contact your event Host for more information.'));
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('party/event'));
        }
    }

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

    public function guestAction() {

        $event = $this->_initEvent();
        if (!$event) {
            return Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('events'));
            return;
        }

        $evenHelper = Mage::helper("julfiker_party/event");
        if ($evenHelper->isAmbassador() || $evenHelper->isHost($event)) {
            Mage::register('current_event', $event);
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
                        'event',
                        array(
                            'label' => $event->getTitle(),
                            'link' => $event->getEventUrl(),
                        )
                    );
                    $breadcrumbBlock->addCrumb(
                        'guest',
                        array(
                            'label' => "Guest RSVP",
                            'link' => '',
                        )
                    );
                }
            }

            $this->renderLayout();
        }
        else {
            return Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('events'));
            return;
        }

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
            Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('party/event/create'));
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
        $data['title'] = Mage::helper("julfiker_party/event")->getEventTitle($data['host']);
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
        $data['url_key'] = $this->_generateUrlKey($data['host']);
        return $data;
    }

    /**
     * Generate unique url for an event
     *
     * @param $customerId
     * @return string
     */
    private function _generateUrlKey($customerId) {
        $customer = Mage::getSingleton('customer/customer')->load($customerId);
        $name = $customer->getName();
        $key = $name;
        $key .= " Event ".time();
        return strtolower(str_replace(" ", "_",$key));
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
