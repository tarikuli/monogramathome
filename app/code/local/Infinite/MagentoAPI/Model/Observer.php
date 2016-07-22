<?php

class Infinite_MagentoAPI_Model_Observer
{
	public function customerLoggedIn($observer)
	{
		$params = Mage::app()->getRequest()->getParams();
		$customer = $observer->getCustomer();
		$apiHelper = Mage::helper('magento_api/api');
		$apiHelper->login($params, $customer);
	}

	public function customerLogout($observer)
	{
		$customer = $observer->getCustomer();
		$apiHelper = Mage::helper('magento_api/api');
		$apiHelper->logout($customer);
	}

	public function customerRegisterSuccess($observer)
	{
		$params = Mage::app()->getRequest()->getParams();
		$customer = $observer->getCustomer();

		$emailParts = explode("@", $params['email']);
		$username = $emailParts[0];
		$customerCollection = Mage::getModel('customer/customer')->getCollection()
			->addAttributeToFilter('username', $username);
		if($customerCollection->count())
			$username .= $customer->getId();

		$customer->setUsername($username);
		$customer->save();

		$apiHelper = Mage::helper('magento_api/api');
		$apiHelper->registration($params, $customer);
	}

	public function customerAccountEditPost($observer)
	{
		$params = Mage::app()->getRequest()->getParams();
		$apiHelper = Mage::helper('magento_api/api');
		$apiHelper->editProfile($params);
	}

	public function customerAddressFormPost($observer)
	{
		$params = Mage::app()->getRequest()->getParams();
		$apiHelper = Mage::helper('magento_api/api');
		$apiHelper->editProfile($params);
	}

	public function checkoutOnepageSuccess($observer)
	{
		$orderIds = $observer->getOrderIds();
		$apiHelper = Mage::helper('magento_api/api');
		$apiHelper->purchase($orderIds);
	}

	public function enableAddressFieldsToRegister($observer)
	{
        $layout = $observer->getEvent()->getLayout();

        if(Mage::app()->getFrontController()->getAction()->getFullActionName() == "customer_account_create")
        {
            $xml = '<reference name="customer_form_register">';
            $xml .= '<action method="setShowAddressFields">';
            $xml .= '<param>true</param>';
            $xml .= '</action>';
            $xml .= '</reference>';
            $layout->getUpdate()->addUpdate($xml);
            $layout->generateXml();
        }
	}
}