<?php
/**
 * Created by PhpStorm.
 * User: julfiker
 * Date: 5/14/17
 * Time: 12:36 AM
 */
class Julfiker_Party_ContactController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
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
                            'label' => Mage::helper('julfiker_party')->__('View all events'),
                            'link' => Mage::getUrl("julfiker_party/event"),
                        )
                    );
                    $breadcrumbBlock->addCrumb(
                        'contacts',
                        array(
                            'label' => Mage::helper('julfiker_party')->__('View all contacts'),
                            'link' => '',
                        )
                    );
                }
            }
            $this->renderLayout();
        }
        else {
            Mage::getSingleton('customer/session')->addError("Accept our apologies . But at this time your access is denied!");
            $this->_redirectReferer();
        }
    }

    public function createAction() {
        if ($this->_checkPermission()) {
            Mage::getSingleton('customer/session')->setGrantAccess(true);
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
                            'label' => Mage::helper('julfiker_party')->__('View all events'),
                            'link' => Mage::getUrl("julfiker_party/event"),
                        )
                    );
                    $breadcrumbBlock->addCrumb(
                        'member',
                        array(
                            'label' => Mage::helper('julfiker_party')->__('Add new contact'),
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
        else {
            Mage::getSingleton('customer/session')->addError("Accept our apologies . But at this time your access is denied!");
            $this->_redirectReferer();
        }

    }

    /**
     * Create customer account action
     */
    public function createPostAction()
    {
        $isGrantAccess = Mage::getSingleton('customer/session')->getGrantAccess();
        $evenHelper = Mage::helper("julfiker_party/event");
        if ($isGrantAccess) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $request = $this->getRequest();
            $y = $request->getPost('year');
            $m = $request->getPost('month');
            $d = $request->getPost('day');
            $date = date('m/j/Y', strtotime("$y-$m-$d"));
            if ($request->isPost()) {
                $contact = Mage::getModel("julfiker_party/contact");
                $contact = $contact->getCollection()
                            ->addFieldToFilter('customer_id', $customerData->getId())
                            ->addFieldToFilter('email', $request->getPost('email'))
                            ->getFirstItem();

                if (!$contact->getId()) {
                    $contact
                        ->setCustomerId($customerData->getId())
                        ->setFirstName($request->getPost('firstname'))
                        ->setLastName($request->getPost('lastname'))
                        ->setMiddleName($request->getPost('middlename'))
                        ->setEmail($request->getPost('email'))
                        ->setDob($date)
                        ->setCountryId($request->getPost('country_id'))
                        ->setPostcode($request->getPost('postcode'))
                        ->setCity($request->getPost('city'))
                        ->setTelephone($request->getPost('telephone'))
                        ->setStreet($request->getPost('street'))
                        ->setNote($request->getPost('note'));

                    if (strtolower($request->getPost('country_id')) == "us")
                        $contact->setRegionId($request->getPost('region_id'));

                    if ($request->getPost('newsletter')) {
                        $newsletter = implode(";", $request->getPost('newsletter'));
                        $contact->setNewsletter($newsletter);
                    }

                    try {
                        $websiteId = Mage::app()->getStore()->getWebsiteId();
                        $contact->setWebsiteId($websiteId);
                        $contact->save();
                        Mage::getSingleton('customer/session')->unsGrantAcccess();
                        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('julfiker_party')->__('Contact was added successfully!'));
                    } catch (Exception $e) {
                        Mage::getSingleton('customer/session')->addError($e->getMessage());
                    }
                } else {
                    Mage::getSingleton('customer/session')->addError("Contact person already added with email (" . $request->getPost('email') . ")");
                }
            }
        }
        else {
            Mage::getSingleton('customer/session')->addError("Accept our apologies . But at this time your access is denied!");
        }
        $this->_redirectReferer();
    }

    /**
     * Add contact permission checking
     *
     * @return bool
     */
    private function _checkPermission() {
        $eventId = ($this->getRequest()->get('event_id'))?$this->getRequest()->get('event_id'):0;
        $_event = Mage::getModel('julfiker_party/event')->load($eventId);
        if (Mage::helper("julfiker_party/event")->isHost($_event))
            return true;
        elseif (Mage::helper("julfiker_party/event")->isAmbassador($_event))
            return true;

        return false;
    }
}