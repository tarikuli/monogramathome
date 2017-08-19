<?php
class IWD_Opc_Order2Controller extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
		echo "<br>".date('l jS \of F Y h:i:s A');
		echo "<br>".Mage::app()->getRequest()->cus_id;
		echo "<br>".Mage::app()->getRequest()->starterkit;
		
		#$data['cus_id'] = Mage::app()->getRequest()->cus_id;
		$customerObject = Mage::getModel ( 'customer/customer' )->load ( Mage::app()->getRequest()->cus_id);
		#echo "<pre>".$customerObject->getDefaultBilling()."</pre>";
		
		$billing = $customerObject->getDefaultBillingAddress();
		$dob = new DateTime($customerObject->getDob());
		
		$data['address_id'] = $customerObject->getDefaultBilling();
		$data['firstname'] = $customerObject->getFirstname();  // 'Mohammad Tarikul',
		$data['middlename'] = $customerObject->getMiddlename(); // 'Islam',
		$data['lastname'] = $customerObject->getLastname(); //'Jewel',
		$data['company'] = $customerObject->getFirstname().' '.$customerObject->getLastname(); //'ERP System',
		$data['email'] = $customerObject->getEmail(); //'ta4rikuli@gmail.com',
		$data['month'] = $dob->format('m'); //'01',
		$data['day'] = $dob->format('d'); //'01',
		$data['year'] = $dob->format('Y'); //'1978',
		$data['dob'] = $dob->format('m/d/Y'); //'01/01/1978',
		$data['ssn_number'] = $customerObject-> getSocialSecurityNumber(); //'111-11-1111',
		$data['street'] = $billing->getStreet();
		$data['city'] = $billing->getCity(); //'Elmhurst',
		$data['region_id'] = $billing->getRegionId(); //43,
		$data['region'] = $billing->getRegion(); //null,
		$data['postcode'] = $billing->getPostcode(); //11373,
		$data['country_id'] = $billing->getCountryId(); //'US',
		$data['telephone'] = $billing->getTelephone(); //'9179071711',
		$data['fax'] = $customerObject-> getAddressId(); //0,
		$data['save_in_address_book'] = 1; //1,
		$data['create_account'] = 0; //1,
		$data['customer_password'] = $customerObject->getPasswordHash(); //'ta4rikuli',
		$data['confirm_password'] = $customerObject-> getPasswordHash(); //'ta4rikuli',
		$data['use_for_shipping'] = 1; //1,
// 		$data['cus_id'] = Mage::app()->getRequest()->cus_id;
					
		$this->cartProductAction($customerObject);
		
			$quote = Mage::getSingleton ( 'checkout/session' )->getQuote ();
			/*
			 * One page checkout consists of following steps
			 * (1) Customer login method aka Checkout method
			 * (2) Billing information (address)
			 * (3) Shipping information (address)
			 * (4) Shipping method
			 * (5) Payment information
			 * (6) Order review, in short: DO THE ORDER
			 */
// 			$storeId = Mage::app ()->getStore ()->getId ();
			
			$checkout = Mage::getSingleton ( 'checkout/type_onepage' );
			
			$checkout->initCheckout ();
			
			$checkout->saveShippingMethod ( 'excellence_excellence' );
			
			$quote->getShippingAddress ()->setShippingMethod ( 'excellence_excellence' );
			
			$quote->getShippingAddress ()->unsGrandTotal (); // clear the values so they won't take part in calculating the totals
			
			$quote->getShippingAddress ()->unsBaseGrandTotal ();
			
			$quote->getShippingAddress ()->setCollectShippingRates ( true )->save ();
			
			$quote->getShippingAddress ()->collectTotals (); // collect totals and ensure the initialization of the shipping methods
			
			$quote->collectTotals ();
			
			// STEP(1)
			$checkout->saveCheckoutMethod ( Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER );
			
			// STEP(2)
			$checkout->saveBilling ( $data, false );
			
			// STEP(3)
			$checkout->saveShipping ( $data, false );
			
			// STEP(4)
			$checkout->saveShippingMethod ( 'tablerate_bestway' );
			
			// STEP(5)
			$checkout->savePayment (Array(
				'method' => 'transarmor',
				'cc_type' => 'VI',
				'cc_number' => '4246 3152 3088 5095',
				'cc_exp_month' => '9',
				'cc_exp_year' => '2019',
				'cc_cid' => '587'
	
			));
			
			Mage::getSingleton ( 'checkout/type_onepage' )->getQuote ()->getShippingAddress ()->setShippingMethod ( 'tablerate_bestway' );
			
			// STEP(6)
			/*
			 * $checkout->saveOrder() returns array holding empty object
			 * of type Mage_Checkout_Model_Type_Onepage
			 */
			
			try {
				
				$checkout->saveOrder ();
				
			} catch ( Exception $ex ) {
				echo "<pre>";
				echo $ex->getMessage ();
				echo "</pre>";
			}
			
			// addCartItems($products_delayed);
			$cart->truncate ();
			$cart->save ();
			Mage::getSingleton ( 'checkout/session' )->setCartWasUpdated ( true );
			Mage::getSingleton ( 'customer/session' )->logout ();
			
			Mage::getSingleton('checkout/session')->clear();
			Mage::getSingleton('customer/session')->clear();
			$customerAddressId = '';
		}
		
		public function cartProductAction($customerObject){
		
			
			/* CUSTOM CODE */
			Mage::getSingleton('checkout/cart')->truncate()->save();
			Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
		
			# Get Kit ID by request onclick="setLocation('{{store url="ambassador/index/index/starterkit/1467"
			$starterKitId = $this->getRequest()->getParam('starterkit');
		
		
			if(isset($starterKitId))
			{
			# Insert KIT in Cart.
				$product = Mage::getModel('catalog/product')->load($starterKitId);
				Mage::getSingleton('checkout/cart')->addProduct($product, array('qty' => 1));
				Mage::getSingleton('checkout/cart')->save();
				Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
			}
		
			## Programmitcally Customer login code use later ##
			Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customerObject);
				
			if (Mage::getSingleton('customer/session')->isLoggedIn()) {
				$custSessionId = Mage::getModel("core/session")->getEncryptedSessionId();
				echo "<br> Custome is login";
			}else {
	        	echo "<br> Custome not login";
			}
			
			## Programmitcally Customer login code use later ##
			
			/* CUSTOM CODE ENDS */
			
	        $quote = Mage::getSingleton ( 'checkout/session' )->getQuote ();
			
			if (!$quote->hasItems() || $quote->getHasError()) {
			$this->_redirect('checkout/cart');
			echo "<pre>1. Cart has error.</pre>"; die();
			}
			
			
			
			
	        if (!$quote->validateMinimumAmount()) {
				$error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
				Mage::getStoreConfig('sales/minimum_order/error_message') :
				Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');
				
				echo "<pre>2. Cart has error</pre>";
					Zend_Debug::dump($error);
					die();
	        }
	        
	        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
	        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure' => true)));
			
			$this->getOnepage()->initCheckout();
		
	}
}