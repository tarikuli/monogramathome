<?php

class Infinite_MagentoAPI_Helper_Oms extends Infinite_MagentoAPI_Helper_Log
{
    const GROUP_AMBASSADOR = "Ambassador";
    const ATTRIBUTE_SET = "Kit";
    const API_URL = "http://www.dashboard.monogramathome.com/backoffice/magento_api";

    protected $_apiUrl;
    private $needExecuted = true;



	public function pushPurchaseToOms($orderIds)
	{
		foreach($orderIds as $orderId)
		{
			$orderObject = Mage::getModel('sales/order')->load($orderId);

			if($orderObject->getCustomerId())
			{
				$customerObject = Mage::getModel('customer/customer')->load($orderObject->getCustomerId());
				$billingAddress = $customerObject->getPrimaryBillingAddress();
				$shippingAddress = $orderObject->getShippingAddress();
// 				$shippingAddress = $customerObject->getPrimaryShippingAddress();
			}
				
// 			$purchaseData[];
			
			$purchaseData['Bill-Address1']	= $billingAddress->getStreet1();
			$purchaseData['Bill-Address2']	= $billingAddress->getStreet2();
			$purchaseData['Bill-City']		= $billingAddress->getCity(); //'Vancouver';
			$purchaseData['Bill-Company']	= '';
			$purchaseData['Bill-Country']	= $billingAddress->getCountryId(); //'US United States';
			$purchaseData['Bill-Email']		= $customerObject->getEmail();
			$purchaseData['Bill-Firstname']	= $billingAddress->getFirstname(); //'Mary';
			$purchaseData['Bill-Lastname']	= $billingAddress->getLastname();  //'Hammond';
			$purchaseData['Bill-Name']		= $billingAddress->getFirstname()." ".$billingAddress->getLastname();
			$purchaseData['Bill-Phone']		= $billingAddress->getTelephone();	//'360-566-5277';
			$purchaseData['Bill-State']		= $billingAddress->getRegion();	//'WA';
			$purchaseData['Bill-Zip']		= $billingAddress->getPostcode();	//'98665';
			$purchaseData['Bill-maillist']	= 'no';
			$purchaseData['Card-Expiry']	= 'xx/xxxx';
			$purchaseData['Card-Name']		= 'PayPal';
			$purchaseData['Comments']		= $orderObject->getCustomerNote();
			$purchaseData['Coupon-Description']	= 'Nil'; // ToDo
			$purchaseData['Coupon-Id']	= $orderObject->getCouponCode(); //'gg4phdjf9kmw4';
			$purchaseData['Coupon-Value']	= '0'; // ToDo
			$purchaseData['Date']	= $orderObject->getCreatedAt(); // 'Fri Apr 28 19:35:27 2017 GMT';
			$purchaseData['ID']	= 'yhst-128796189915726-'.$orderObject->getIncrementId();
			$purchaseData['IP']	= $orderObject->getRemoteIp();	//'67.171.239.95';
			
			$orderItems = $orderObject->getAllItems();
			$index = 1;
			foreach($orderItems as $item)
			{
				$productId = $item->getProductId();
				$productObject = Mage::getModel('catalog/product')->load($productId);
				
				$purchaseData['Item-Code-'.$index]	= $item->getSku();
				$purchaseData['Item-Description-'.$index]	= $item->getName();
				$purchaseData['Item-Id-'.$index]	= $productObject->getUrlKey();
				$purchaseData['Item-Quantity-'.$index]	= $item->getQtyOrdered();
				$purchaseData['Item-Taxable-'.$index]	= 'Yes';
				$purchaseData['Item-Unit-Price-'.$index]	= $item->getRowTotal();
				$purchaseData['Item-Url-'.$index]	= "http://shop.monogramathome.com/".$productObject->getUrlPath();
				$purchaseData['Item-Thumb-'.$index]	= $productObject->getImageUrl();
				
				# Another for loop for Parameter Options
				$itemOptions = $item->getProductOptions();

				foreach ($itemOptions['options'] as $value){
// 				foreach ($item->getProductOptions() as $key => $value){
// 					echo "<br>Item-Option-".$index."-".$value['label']." ".$value['print_value'];
					$purchaseData['Item-Option-'.$index.'-'.trim(str_replace(":", "", $value['label']))] = $value['print_value'];
				}
				
				$index ++;
			}
					
			$purchaseData['Item-Count']	= ($index-1);
			$purchaseData['Numeric-Time']	= strtotime($orderObject->getCreatedAt()); //'1493408127';
			$purchaseData['PayPal-Address-Status']	= 'Confirmed';
			$purchaseData['PayPal-Auth']	= '8F4701569X6000947';
			$purchaseData['PayPal-Merchant-Email']	= 'pablo@dealtowin.com';
			$purchaseData['PayPal-Payer-Status']	= 'Unverified';
			$purchaseData['PayPal-TxID']	= '75692712YB5948433';

						
			$purchaseData['Ship-Address1']	= $shippingAddress->getStreet1();	//'3701 NE 94th st';
			$purchaseData['Ship-Address2']	= $shippingAddress->getStreet2();
			$purchaseData['Ship-City']	= $shippingAddress->getCity(); //'Vancouver';
			$purchaseData['Ship-Company']	= '';
			$purchaseData['Ship-Country']	= $shippingAddress->getCountryId(); //'US United States';
			$purchaseData['Ship-Firstname']	= $shippingAddress->getFirstname(); //'Mary';
			$purchaseData['Ship-Lastname']	= $shippingAddress->getLastname();  //'Hammond';
			$purchaseData['Ship-Name']	= $shippingAddress->getFirstname()." ".$billingAddress->getLastname();
			$purchaseData['Ship-Phone']	= $shippingAddress->getTelephone();	//'360-566-5277';
			$purchaseData['Ship-State']	=  $shippingAddress->getRegion();	//'WA';
			$purchaseData['Ship-Zip']	=  $shippingAddress->getPostcode();	//'98665';
			$purchaseData['Shipping']	= 'USPS FIRST CLASS MAIL'; // ToDo
			$purchaseData['Shipping-Charge']	= $orderObject->getShippingAmount(); //'17.95';
			$purchaseData['Space-Id']	= ''; // ToDo
			$purchaseData['Store-Id']	= 'yhst-128796189915726'; // ToDo
			$purchaseData['Store-Name']	= 'monogramonline.com'; // ToDo
			$purchaseData['Tax-Charge']	= $orderObject->getBaseTaxAmount(); //'0.00';
			$purchaseData['Total']	= $orderObject->getGrandTotal(); // '17.95';

			echo "<pre>1XXXXXX"; print_r($purchaseData); echo "</pre>";	
					
			$client = new Zend_Http_Client('http://5p.monogramonline.com/hook');
			$client->setMethod(Zend_Http_Client::POST);
			$client->setParameterPost($purchaseData);
			$json = $client->request()->getBody();
			
			echo "<pre>2XXXXXX"; print_r($json); echo "</pre>"; die();

		}
	}



}