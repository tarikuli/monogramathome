<?php

class Infinite_MagentoAPI_Model_Observer
{
	public function checkoutCartAttribute($observer)
	{

		$cart = Mage::getSingleton('checkout/session')->getQuote();
	

	
		foreach ($cart->getAllVisibleItems() as $item) 
		{ 
			
		   /** Adding to queue processing multi store dynamically */
			$product = Mage::getModel('catalog/product')->load($item->getProductId());
			$attributeSetModel = Mage::getModel("eav/entity_attribute_set");
			$attributeSetModel->load($product->getAttributeSetId());
			$attributeSetName  = $attributeSetModel->getAttributeSetName();
		  
			if(0 == strcmp($attributeSetName, "Kit")) {
				$attributeCheck[] = 1;
			}else{
				$attributeCheck[] = 0;
			}
		   
		 }
	
		/* Check If any non kit Product exist */
		if((count($cart->getAllVisibleItems()) > 1) &&  in_array(1, $attributeCheck)) {
			/* If only KIT in Product then Add to QUE for create sub domain */
			Mage::getSingleton('checkout/cart')->truncate();
			Mage::getSingleton('core/session')->addError('Cannot add the Kit and General item to shopping cart.');
		}
	
	}
	
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

	public function saveBillingDetail()
	{
		$data = Mage::app()->getRequest()->getPost('billing', array());
		if(isset($data['customer_password']) && $data['customer_password'] != "")
		{
			Mage::getSingleton('core/session')->setCurrentCheckoutCustomerPassword($data['customer_password']);
		}
	}
}