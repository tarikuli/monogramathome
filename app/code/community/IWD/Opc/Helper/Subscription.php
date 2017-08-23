<?php
class IWD_Opc_Helper_Subscription extends Mage_Checkout_Helper_Url{

	const XML_PATH_DEFAULT_PAYMENT = 'opc/default/payment';
	
    /**
     * Monthly Subscription
     *
     * @return string
     */
    public function submitSubscription($productId, $cusEmail){

    	$productIds = array (
    			$productId => 1
    	);
    	
    	 Mage::log('submitSubscription 1 = '.print_r($productIds, true), null, 'system.log', true);
    	 Mage::log('submitSubscription 2 = '.print_r($cusEmail, true), null, 'system.log', true);
    	 
    	 $result = $this->indexAction($productIds, $cusEmail);
    	 Mage::log('submitSubscription 0 = '.$result, null, 'system.log');
    }
    
    public function indexAction($productIds, $cusEmail) {
    
    	$customerObject = Mage::getModel ( 'customer/customer' )->loadByEmail (trim($cusEmail));
    	
    	if(empty($customerObject)){
    		return "Email  user not found.";
    	}
    	
    	if(!Mage::getSingleton('core/session')->getAmbassadorPayInfo()){
    		return "getAmbassadorPayInfo not found.";
    	}
#Mage::log('submitSubscription 3 = '.print_r($customerObject->toArray(), true), null, 'system.log', true);

    	$billing = $customerObject->getDefaultBillingAddress ();
    
    	$addressArray = array (
    			'customer_address_id' => '',
    			'prefix' => '',
    			'firstname' => $customerObject->getFirstname (),
    			'middlename' => $customerObject->getMiddlename (),
    			'lastname' => $customerObject->getLastname (),
    			'suffix' => '',
    			'company' => '',
    			'street' => $billing->getStreet (), 
    			'city' => $billing->getCity (),
    			'country_id' => $billing->getCountryId (), // two letters country code
    			'region' => $billing->getRegion (), // can be empty '' if no region
    			'region_id' => $billing->getRegionId (), // can be empty '' if no region_id
    			'postcode' => $billing->getPostcode (),
    			'telephone' => $billing->getTelephone (),
    			'fax' => '',
    			'save_in_address_book' => 1
    	);
    	
Mage::log('submitSubscription 4 = '.print_r($addressArray, true), null, 'system.log', true);

//     	$savePayment = Array (
//     			'method' => self::XML_PATH_DEFAULT_PAYMENT,
// //     			'cc_type' => 'VI',
// //     			'cc_number' => '4246315230885095',
// //     			'cc_exp_month' => '9',
// //     			'cc_exp_year' => '2019',
// //     			'cc_cid' => '587'
//     	)
//     	;

		$savePayment=[];
	    if(Mage::getSingleton('core/session')->getAmbassadorPayInfo()){
	    	$savePayment = Mage::getSingleton('core/session')->getAmbassadorPayInfo();
	    }else{
	    	return "getAmbassadorPayInfo not set.";
	    }
    
    	$this->cartProductAction ($productIds, $customerObject, $addressArray , $savePayment);
    	    
    }
    
    public function cartProductAction($productIds, $customerObject, $addressArray, $savePayment) {
    
    	/* CUSTOM CODE */
    	Mage::getModel('core/config')->saveConfig('carriers/flatrate/active', '1');
    	Mage::app()->getCacheInstance()->cleanType('config');
    	 
    	Mage::getSingleton ( 'checkout/cart' )->truncate ()->save ();
    	Mage::getSingleton ( 'checkout/session' )->setCartWasUpdated ( true );
    
    	$firstName = $data ['firstname'];
    	$lastName = $data ['lastname'];
    	$email = $data ['email'];
    	$logFileName = 'system.log';
    	
    	$store = $customerObject->getStoreId();
    	Mage::log ( "1. store_id = ".$store, null, $logFileName );
    	
    	$website = $customerObject->getWebsiteId();
    	Mage::log ( "2. website_id = ".$website, null, $logFileName );

    
    	$billingAddress = $addressArray;
    
    	$shippingAddress = $addressArray;
    
    
    	/**
    	 * You need to enable this method from Magento admin
    	 * Other methods: tablerate_tablerate, freeshipping_freeshipping, flatrate_flatrate, tablerate_bestway, etc.
    	 */
    	$shippingMethod = 'flatrate_flatrate';
    
    	/**
    	 * You need to enable this method from Magento admin
    	 * Other methods: checkmo, free, banktransfer, ccsave, purchaseorder, etc.
    	 */
    	#$paymentMethod = 'cashondelivery';
    	$paymentMethod = $savePayment['method'];
    
    	/**
    	 * Array of your product ids and quantity
    	 * array($productId => $qty)
    	 * In the array below, the product ids are 374 and 375 with quantity 3 and 1 respectively
    	*/
//     	$productIds = array (
//     			1744 => 1
//     	);
    
    	// Initialize sales quote object
    	$quote = Mage::getModel ( 'sales/quote' )->setStoreId ( $store );
    
    	// Set currency for the quote
    	$quote->setCurrency ( Mage::app ()->getStore ()->getBaseCurrencyCode () );
    
//     	$customer = Mage::getModel ( 'customer/customer' )->setWebsiteId ( $website )->loadByEmail ( $email );
    
//     	echo "<br>4. customer/customer = "; echo "<pre>"; print_r($customer->toArray()); echo "</pre>";
    
    	// Assign customer to quote
    	$quote->assignCustomer ( $customerObject );
    
    	// Add products to quote
    	foreach ( $productIds as $productId => $qty ) {
    		$product = Mage::getModel ( 'catalog/product' )->load ( $productId );
    		$quote->addProduct ( $product, $qty );
    
    		/**
    		 * Varien_Object can also be passed as the second parameter in addProduct() function like below:
    		 * $quote->addProduct($product, new Varien_Object(array('qty' => $qty)));
    		*/
    	}
//     	echo "<br>5. Product added done";
    	// Add billing address to quote
    	$billingAddressData = $quote->getBillingAddress ()->addData ( $billingAddress );
//     	echo "<br>6. Add billing address to quote";
    
    	// Add shipping address to quote
    	$shippingAddressData = $quote->getShippingAddress ()->addData ( $shippingAddress );
//     	echo "<br>7.Add shipping address to quote";
    
    	/**
    	 * Billing or Shipping address for already registered customers can be fetched like below
    	 *
    	 * $customerBillingAddress = $customer->getPrimaryBillingAddress();
    	 * $customerShippingAddress = $customer->getPrimaryShippingAddress();
    	 *
    	 * Instead of the custom address, you can add these customer address to quote as well
    	 *
    	 * $billingAddressData = $quote->getBillingAddress()->addData($customerBillingAddress);
    	 * $shippingAddressData = $quote->getShippingAddress()->addData($customerShippingAddress);
    	 */
    
    	Mage::getModel('core/config')->saveConfig('carriers/flatrate/active', '1');
    	Mage::app()->getCacheInstance()->cleanType('config');
    	
    	// Collect shipping rates on quote shipping address data
    	$shippingAddressData->setCollectShippingRates ( true )->collectShippingRates ();
//     	echo "<br>8. Collect shipping rates on quote shipping address data";
    
    	// Set shipping and payment method on quote shipping address data
//     	$shippingAddressData->setShippingMethod ( $shippingMethod )
//     						->setPaymentMethod ( $paymentMethod );
    	
    	$shippingAddressData->removeAllShippingRates()
					    	->setCollectShippingRates(true)
					    	->setShippingMethod('tablerate_bestway')
					    	->setShippingDescription('Table Rate - Best Way')
					    	->setPaymentMethod ( $paymentMethod );
    	
    	
//     	echo "<br>9. Set shipping and payment method on quote shipping address data";
    
    	// Set payment method for the quote
    	// 		$quote->getPayment ()->importData ( array (
    	// 				'method' => $paymentMethod
    	// 		) );
    
    	Mage::getSingleton('core/session')->setAmbassadorPayInfo($savePayment);
    	$quote->getPayment ()->importData ($savePayment);
    	
//     	echo "<br>10. Set payment method for the quote";
    
    	try {
    		// Collect totals of the quote
    		$quote->collectTotals ();
    			
    		// Save quote
    		$quote->save ();
    			
    		// Create Order From Quote
    		$service = Mage::getModel ( 'sales/service_quote', $quote );
    		$service->submitAll ();
    		$incrementId = $service->getOrder ()->getRealOrderId ();
    			
    		Mage::getSingleton ( 'checkout/session')->setLastQuoteId ( $quote->getId () )
    												->setLastSuccessQuoteId ( $quote->getId () )
    												->clearHelperData ();
    			
    		/**
    		 * For more details about saving order
    		 * See saveOrder() function of app/code/core/Mage/Checkout/Onepage.php
    		*/
    			
    		// Log order created message
    		Mage::log ( 'Order created with increment id: ' . $incrementId, null, $logFileName );
    			
    		$result ['success'] = true;
    		$result ['error'] = false;

    		Mage::getModel('core/config')->saveConfig('carriers/flatrate/active', '0');
    		Mage::app()->getCacheInstance()->cleanType('config');
    		
    	} catch ( Mage_Core_Exception $e ) {
    		Mage::getModel('core/config')->saveConfig('carriers/flatrate/active', '0');
    		Mage::app()->getCacheInstance()->cleanType('config');
    		
    		Mage::log('Details saving order1 = '.print_r($e->toArray(), true), null, $logFileName, true);
    		return $e->getMessage();
    	} catch ( Exception $e ) {
    		Mage::getModel('core/config')->saveConfig('carriers/flatrate/active', '0');
    		Mage::app()->getCacheInstance()->cleanType('config');
    		
    		Mage::log('Details saving order1 = '.print_r($e->toArray(), true), null, $logFileName, true);
    		Mage::logException ( $e);
    		return $e->getMessage();
    	}
    	
    	return 'Order created with increment id: ' . $incrementId;
    }

}
