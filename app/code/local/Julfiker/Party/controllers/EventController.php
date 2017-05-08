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
 * Event front contrller
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_EventController extends Mage_Core_Controller_Front_Action
{

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
// elseif (!$event->getStatus()) {
//            return false;
//        }
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
                        'label' => Mage::helper('julfiker_party')->__('Book an event'),
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
                $data = $this->_filterDateTime($data, array('start_at' ,'end_at'));
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

    public function joinAction() {
        $request = $this->getRequest();
        $status = $request->get('status');
        $email = $request->get('email');
        $event = $request->get('event');
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
        }
        else if ($data['loc_type'] == "diff") {
            $address = Mage::getModel('customer/address')->load($data['addressId']);
        }
        //var_dump($address);
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
