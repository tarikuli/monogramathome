<?php

class Julfiker_Party_EventController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        Mage::getSingleton('core/session', array('name' => 'frontend'));

        $sessionCustomer = Mage::getSingleton("customer/session");

        if($sessionCustomer->isLoggedIn()) {
            $websitecode = Mage::app()->getWebsite()->getCode();
            $currentAmbassadorCode = Mage::getSingleton('core/session')->getAmbassadorCode();
            if ($websitecode == $currentAmbassadorCode) {
                $params = $this->getRequest()->getParams();
                $key = ($params) ? key($params) : "";
                //$this->getLayout()->getBlock('party')->setKey($key);
            }
           else {
               Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('customer/account'));
           }
         } else {
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('customer/account/login'));
        }

        $this->renderLayout();
    }

    public function createAction() {
        $this->loadLayout();
        $this->renderLayout();
        //Todo: Rending event form
    }

    public function saveAction() {
        $this->loadLayout();
        $this->renderLayout();
        //Todo: save  event
    }
}