<?php
class IWD_Opc_Order2Controller extends Mage_Core_Controller_Front_Action {
	const XML_PATH_DEFAULT_PAYMENT = 'opc/default/payment';
	const XML_PATH_TITLE = 'opc/global/title';
	const XML_PATH_GEO_COUNTRY = 'opc/geo/country';
	const XML_PATH_GEO_CITY = 'opc/geo/city';
	const KEYSALT = "aghtUJ6y";
	
	/* @var $_order Mage_Sales_Model_Order */
	protected $_order;
	
	/**
	 * Checkout page
	 * One page checkout consists of following steps
	 * (1) Customer login method aka Checkout method
	 * (2) Billing information (address)
	 * (3) Shipping information (address)
	 * (4) Shipping method
	 * (5) Payment information
	 * (6) Order review, in short: DO THE ORDER
	 * // STEP(1)
	 * $checkout->saveCheckoutMethod('guest');
	 * // STEP(2)
	 * $checkout->saveBilling($billingAddress, false);
	 * // STEP(3)
	 * $checkout->saveShipping($shippingAddress, false);
	 * // STEP(4)
	 * $checkout->saveShippingMethod('flatrate_flatrate');
	 * // STEP(5)
	 * $checkout->savePayment(array('method'=>'checkmo'));
	 * // STEP(6)
	 * $checkout->saveOrder() returns array holding empty object of type Mage_Checkout_Model_Type_Onepage
	 * $checkout->saveOrder();
	 *
	 * http://www.monogramathome.com/ambassador/order/index/starterkit/1744/cus_id/77
	 */
	public function indexAction() {
		echo "<br>" . date ( 'l jS \of F Y h:i:s A' );
// 		echo "<br>" . Mage::app ()->getRequest ()->cus_id;
// 		echo "<br>" . Mage::app ()->getRequest ()->starterkit;
		
		// $data['cus_id'] = Mage::app()->getRequest()->cus_id;
		$customerObject = Mage::getModel ( 'customer/customer' )->load ( Mage::app ()->getRequest ()->cus_id );
		// echo "<pre>".$customerObject->getDefaultBilling()."</pre>";
		
		$billing = $customerObject->getDefaultBillingAddress ();
		$dob = new DateTime ( $customerObject->getDob () );
		
		$data ['address_id'] = $customerObject->getDefaultBilling ();
		$data ['firstname'] = $customerObject->getFirstname (); // 'Mohammad Tarikul',
		$data ['middlename'] = $customerObject->getMiddlename (); // 'Islam',
		$data ['lastname'] = $customerObject->getLastname (); // 'Jewel',
		$data ['company'] = $customerObject->getFirstname () . ' ' . $customerObject->getLastname (); // 'ERP System',
		$data ['email'] = $customerObject->getEmail (); // 'ta4rikuli@gmail.com',
		$data ['month'] = $dob->format ( 'm' ); // '01',
		$data ['day'] = $dob->format ( 'd' ); // '01',
		$data ['year'] = $dob->format ( 'Y' ); // '1978',
		$data ['dob'] = $dob->format ( 'm/d/Y' ); // '01/01/1978',
		$data ['ssn_number'] = $customerObject->getSocialSecurityNumber (); // '111-11-1111',
		$data ['street'] = $billing->getStreet ();
		
		$data ['city'] = $billing->getCity (); // 'Elmhurst',
		$data ['region_id'] = $billing->getRegionId (); // 43,
		$data ['region'] = $billing->getRegion (); // null,
		$data ['postcode'] = $billing->getPostcode (); // 11373,
		$data ['country_id'] = $billing->getCountryId (); // 'US',
		$data ['telephone'] = $billing->getTelephone (); // '9179071711',
		$data ['fax'] = $customerObject->getAddressId (); // 0,
		$data ['save_in_address_book'] = 1; // 1,
		$data ['create_account'] = 0; // 1,
		$data ['customer_password'] = $customerObject->getPasswordHash (); // 'ta4rikuli',
		$data ['confirm_password'] = $customerObject->getPasswordHash (); // 'Unable to store RP credit card',
		$data ['use_for_shipping'] = 1; // 1,
		$data ['cus_id'] = Mage::app ()->getRequest ()->cus_id;
		
		$savePayment = Array (
				'method' => 'transarmor',
				'cc_type' => 'VI',
				'cc_number' => '4246315230885095',
				'cc_exp_month' => '9',
				'cc_exp_year' => '2019',
				'cc_cid' => '587' 
		)
		;
		
		// echo "<pre>";
		// print_r($data);
		// echo "</pre>"; exit();
		
		$this->cartProductAction ( $customerObject, $data , $savePayment);
		echo "<br>" . date ( 'l jS \of F Y h:i:s A' );
		
		exit ();
	}
	public function cartProductAction($customerObject, $data, $savePayment) {
		
		/* CUSTOM CODE */
		Mage::getSingleton ( 'checkout/cart' )->truncate ()->save ();
		Mage::getSingleton ( 'checkout/session' )->setCartWasUpdated ( true );
		
		$store = $customerObject->getStoreId();
		echo "<br>1. store_id =".$store;		
		
		$website = $customerObject->getWebsiteId();
		echo "<br>2. website_id =".$website;
				
		$firstName = $data ['firstname'];
		$lastName = $data ['lastname'];
		$email = $data ['email'];
		$logFileName = 'system.log';
		
		$billingAddress = array (
				'customer_address_id' => '',
				'prefix' => '',
				'firstname' => $firstName,
				'middlename' => '',
				'lastname' => $lastName,
				'suffix' => '',
				'company' => '',
				'street' => $data ['street'], // optional
				'city' => $data ['city'],
				'country_id' => $data ['country_id'], // two letters country code
				'region' => $data ['region'], // can be empty '' if no region
				'region_id' => $data ['region_id'], // can be empty '' if no region_id
				'postcode' => $data ['postcode'],
				'telephone' => $data ['telephone'],
				'fax' => '',
				'save_in_address_book' => 1 
		);
		echo "<br>3. billingAddress = "; echo "<pre>"; print_r($billingAddress); echo "</pre>";
	
		$shippingAddress = array (
				'customer_address_id' => '',
				'prefix' => '',
				'firstname' => $firstName,
				'middlename' => '',
				'lastname' => $lastName,
				'suffix' => '',
				'company' => '',
				'street' => $data ['street'], // optional
				'city' => $data ['city'],
				'country_id' => $data ['country_id'], // two letters country code
				'region' => $data ['region'], // can be empty '' if no region
				'region_id' => $data ['region_id'], // can be empty '' if no region_id
				'postcode' => $data ['postcode'],
				'telephone' => $data ['telephone'],
				'fax' => '',
				'save_in_address_book' => 1 
		);
		
		echo "<br>3. shippingAddress = "; echo "<pre>"; print_r($shippingAddress); echo "</pre>";
		
		/**
		 * You need to enable this method from Magento admin
		 * Other methods: tablerate_tablerate, freeshipping_freeshipping, flatrate_flatrate, tablerate_bestway, etc.
		 */
		$shippingMethod = 'tablerate_bestway';
		
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
		$productIds = array (
				1744 => 1 
		);
		
		// Initialize sales quote object
		$quote = Mage::getModel ( 'sales/quote' )->setStoreId ( $store );
		
		// Set currency for the quote
		$quote->setCurrency ( Mage::app ()->getStore ()->getBaseCurrencyCode () );
		
		$customer = Mage::getModel ( 'customer/customer' )->setWebsiteId ( $website )->loadByEmail ( $email );
		
		echo "<br>4. customer/customer = "; echo "<pre>"; print_r($customer->toArray()); echo "</pre>";
		
// 		/**
// 		 * Setting up customer for the quote
// 		 * if the customer is not already registered
// 		 */
// 		if (! $customer->getId ()) {
// 			$customer = Mage::getModel ( 'customer/customer' );
			
// 			$customer->setWebsiteId ( $website )->setStore ( $store )->setFirstname ( $firstName )->setLastname ( $lastName )->setEmail ( $email );
			
// 			/**
// 			 * Creating new customer
// 			 * This is optional.
// 			 * You may or may not create/save new customer.
// 			 * If you don't need to create new customer, you may skip/remove the below try-catch blocks.
// 			 */
// 			try {
// 				// you can write your custom password here instead of magento generated password
// 				$password = $customer->generatePassword ();
// 				$customer->setPassword ( $password );
				
// 				// set the customer as confirmed
// 				$customer->setForceConfirmed ( true );
				
// 				// save customer
// 				$customer->save ();
				
// 				$customer->setConfirmation ( null );
// 				$customer->save ();
				
// 				// set customer address
// 				$customerId = $customer->getId ();
// 				$customAddress = Mage::getModel ( 'customer/address' );
// 				$customAddress->setData ( $billingAddress )->setCustomerId ( $customerId )->setIsDefaultBilling ( '1' )->setIsDefaultShipping ( '1' )->setSaveInAddressBook ( '1' );
				
// 				// save customer address
// 				$customAddress->save ();
				
// 				// send new account email to customer
// 				// $customer->sendNewAccountEmail();
// 				$storeId = $customer->getSendemailStoreId ();
// 				$customer->sendNewAccountEmail ( 'registered', '', $storeId );
				
// 				// set password remainder email if the password is auto generated by magento
// 				$customer->sendPasswordReminderEmail ();
				
// 				// auto login customer
// 				// Mage::getSingleton('customer/session')->loginById($customer->getId());
				
// 				Mage::log ( 'Customer with email ' . $email . ' is successfully created.', null, $logFileName );
// 			} catch ( Mage_Core_Exception $e ) {
// 				if (Mage::getSingleton ( 'customer/session' )->getUseNotice ( true )) {
// 					Mage::getSingleton ( 'customer/session' )->addNotice ( Mage::helper ( 'core' )->escapeHtml ( $e->getMessage () ) );
// 				} else {
// 					$messages = array_unique ( explode ( "\n", $e->getMessage () ) );
// 					foreach ( $messages as $message ) {
// 						Mage::getSingleton ( 'customer/session' )->addError ( Mage::helper ( 'core' )->escapeHtml ( $message ) );
// 					}
// 				}
// 			} catch ( Exception $e ) {
// 				// Zend_Debug::dump($e->getMessage());
// 				Mage::getSingleton ( 'customer/session' )->addException ( $e, $this->__ ( 'Cannot add customer' ) );
// 				Mage::logException ( $e );
// 				// $this->_goBack();
// 			}
// 		}
		
		// Assign customer to quote
		$quote->assignCustomer ( $customer );
		
		// Add products to quote
		foreach ( $productIds as $productId => $qty ) {
			$product = Mage::getModel ( 'catalog/product' )->load ( $productId );
			$quote->addProduct ( $product, $qty );
		
		/**
		 * Varien_Object can also be passed as the second parameter in addProduct() function like below:
		 * $quote->addProduct($product, new Varien_Object(array('qty' => $qty)));
		 */
		}
		echo "<br>5. Product added done";
		// Add billing address to quote
		$billingAddressData = $quote->getBillingAddress ()->addData ( $billingAddress );
		echo "<br>6. Add billing address to quote";
		
		// Add shipping address to quote
		$shippingAddressData = $quote->getShippingAddress ()->addData ( $shippingAddress );
		echo "<br>7.Add shipping address to quote";
		
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
		
		// Collect shipping rates on quote shipping address data
		$shippingAddressData->setCollectShippingRates ( true )->collectShippingRates ();
		echo "<br>8. Collect shipping rates on quote shipping address data";
		
		// Set shipping and payment method on quote shipping address data
		$shippingAddressData->setShippingMethod ( $shippingMethod )
							->setPaymentMethod ( $paymentMethod );
		echo "<br>9. Set shipping and payment method on quote shipping address data";
		
		// Set payment method for the quote
// 		$quote->getPayment ()->importData ( array (
// 				'method' => $paymentMethod 
// 		) );

		Mage::getSingleton('core/session')->setAmbassadorPayInfo($savePayment);
		$quote->getPayment ()->importData ($savePayment);
		echo "<br>10. Set payment method for the quote";
		
		try {
			// Collect totals of the quote
			$quote->collectTotals ();
			
			// Save quote
			$quote->save ();
			
			// Create Order From Quote
			$service = Mage::getModel ( 'sales/service_quote', $quote );
			$service->submitAll ();
			$incrementId = $service->getOrder ()->getRealOrderId ();
			
			echo "<br>11. incrementId = ". $incrementId;
			
			Mage::getSingleton ( 'checkout/session' )->setLastQuoteId ( $quote->getId () )->setLastSuccessQuoteId ( $quote->getId () )->clearHelperData ();
			
			/**
			 * For more details about saving order
			 * See saveOrder() function of app/code/core/Mage/Checkout/Onepage.php
			 */
			
			// Log order created message
			Mage::log ( 'Order created with increment id: ' . $incrementId, null, $logFileName );
			
			$result ['success'] = true;
			$result ['error'] = false;
			
			// $redirectUrl = Mage::getSingleton('checkout/session')->getRedirectUrl();
			// $redirectUrl = Mage::getUrl('checkout/onepage/success');
			// $result['redirect'] = $redirectUrl;
			
			// Show response
// 			Mage::app ()->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $result ) )
// 						// ->setRedirect($redirectUrl)s
// 						->endResponse ();
			
			// $this->_redirect('checkout/onepage/success', array('_secure'=>true));
			// $this->_redirect($redirectUrl);
			
			echo "<br>12. sales/service_quote = "; echo "<pre>"; print_r($result); echo "</pre>";
		} catch ( Mage_Core_Exception $e ) {
			$result ['success'] = false;
			$result ['error'] = true;
			$result ['error_messages'] = $e->getMessage ();
			Mage::app ()->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $result ) );
			
			if (Mage::getSingleton ( 'checkout/session' )->getUseNotice ( true )) {
				Mage::getSingleton ( 'checkout/session' )->addNotice ( Mage::helper ( 'core' )->escapeHtml ( $e->getMessage () ) );
			} else {
				$messages = array_unique ( explode ( "\n", $e->getMessage () ) );
				foreach ( $messages as $message ) {
					Mage::getSingleton ( 'checkout/session' )->addError ( Mage::helper ( 'core' )->escapeHtml ( $message ) );
				}
			}
		} catch ( Exception $e ) {
			$result ['success'] = false;
			$result ['error'] = true;
			$result ['error_messages'] = $this->__ ( 'There was an error processing your order. Please contact us or try again later.' );
			Mage::app ()->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $result ) );
			
			Mage::logException ( $e );
			// $this->_goBack();
		}
	}
}