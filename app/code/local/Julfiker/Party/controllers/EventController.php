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
        $event     = Mage::getModel('julfiker_party/event')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($eventId);
        if (!$event->getId()) {
            return false;
        } elseif (!$event->getStatus()) {
            return false;
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
}
