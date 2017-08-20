<?php
class IWD_Opc_Order2Controller extends Mage_Core_Controller_Front_Action{
	
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
	 * http://www.monogramathome.com/ambassadorTest/order/index/starterkit/1744/cus_id/77
	 * 
	 */
	
	public function indexAction(){
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
			$data['cus_id'] = Mage::app()->getRequest()->cus_id;
			
			$savePayment = Array(
				'method' => 'transarmor',
				'cc_type' => 'VI',
				'cc_number' => '4246 3152 3088 5095',
				'cc_exp_month' => '9',
				'cc_exp_year' => '2019',
				'cc_cid' => '587'
	
			);
		
// 		echo "<pre>";
// 		print_r($data);
// 		echo "</pre>"; exit();

		// STEP(0)
		$this->paymentsAction();
			
		// STEP(1)
		# indexAction
		$this->cartProductAction($customerObject);
		Mage::log('cartProductAction done', null, 'system.log', true);
				
		$this->saveGeneralAction($data);
		Mage::log('saveGeneralAction done', null, 'system.log', true);
		
		// STEP(2)
		$this->saveBillingAction($data);
		Mage::log('saveBillingAction done', null, 'system.log', true);
		
		// STEP(3)
		$this->saveShippingAction($data);
		Mage::log('saveShippingAction done', null, 'system.log', true);
		
		// STEP(4)
		$this->saveShippingMethodAction($data);
		Mage::log('saveShippingMethodAction done', null, 'system.log', true);
		
		$savePayment = Array(
			'method' => 'transarmor',
			'cc_type' => 'VI',
			'cc_number' => '4246 3152 3088 5095',
			'cc_exp_month' => '9',
			'cc_exp_year' => '2019',
			'cc_cid' => '587'
				
		);
		
		// STEP(5)
		$this->savePaymentAction($savePayment);
		
		$this->saveShippingMethodAction($data);
		
		// STEP(6)
		$this->saveOrderAction($savePayment);
		echo "<br>".date('l jS \of F Y h:i:s A');
		
		/* CUSTOM CODE */
		$this->_getCart()->truncate()->save();
		$this->_getSession()->setCartWasUpdated(true);
		exit();
	}
	
	
	public function cartProductAction($customerObject){

        /* CUSTOM CODE */
        $this->_getCart()->truncate()->save();
        $this->_getSession()->setCartWasUpdated(true);

        # Get Kit ID by request onclick="setLocation('{{store url="ambassador/index/index/starterkit/1467"
        $starterKitId = $this->getRequest()->getParam('starterkit');
        
        
        if(isset($starterKitId))
        {
        	# Insert KIT in Cart.
	        $product = Mage::getModel('catalog/product')->load($starterKitId);
			$this->_getCart()->addProduct($product, array('qty' => 1));
			$this->_getCart()->save();
			$this->_getSession()->setCartWasUpdated(true);
        }

        Mage::getSingleton('core/session')->unsAmbassadorWebsiteName();
        Mage::getSingleton('core/session')->unsAmbassadorBillingInfo();
        Mage::getSingleton('core/session')->unsAmbassadorProfileInfo();

        #$customerObject = Mage::getModel ( 'customer/customer' )->load ( $data['cus_id'] );
        Mage::getSingleton('core/session')->setAmbassadorWebsiteName($customerObject->getUsername());
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

        $quote = $this->getOnepage()->getQuote();

        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            echo "<pre>1. Cart has error.</pre>"; die();
        }
        
        // init default address
        $this->initDefaultAddress();

        Mage::app()->getCacheInstance()->cleanType('layout');
        
        $this->updateDefaultPayment();
        
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
		
	/**
	 * Get Order by quoteId
	 *
	 * @return Mage_Sales_Model_Order
	 */
	protected function _getOrder(){
		if (is_null($this->_order)) {
			$this->_order = Mage::getModel('sales/order')->load($this->getOnepage()->getQuote()->getId(), 'quote_id');
			if (!$this->_order->getId()) {
				throw new Mage_Payment_Model_Info_Exception(Mage::helper('core')->__("Can not create invoice. Order was not found."));
			}
		}
		return $this->_order;
	}
	
	/**
	 * Create invoice
	 *
	 * @return Mage_Sales_Model_Order_Invoice
	 */
	protected function _initInvoice()
	{
		$items = array();
		foreach ($this->_getOrder()->getAllItems() as $item) {
			$items[$item->getId()] = $item->getQtyOrdered();
		}
		/* @var $invoice Mage_Sales_Model_Service_Order */
		$invoice = Mage::getModel('sales/service_order', $this->_getOrder())->prepareInvoice($items);
		$invoice->setEmailSent(true)->register();
	
		Mage::register('current_invoice', $invoice);
		return $invoice;
	}
	

	
	protected function _getCart(){
		return Mage::getSingleton('checkout/cart');
	}
	
	
	protected function _getSession(){
		return Mage::getSingleton('checkout/session');
	}
	
	protected function _getQuote(){
		return $this->_getCart()->getQuote();
	}
	
	/**
	 * Get one page checkout model
	 *
	 * @return Mage_Checkout_Model_Type_Onepage
	 */
	public function getOnepage(){
		return Mage::getSingleton('checkout/type_onepage');
	}
	
	protected function _ajaxRedirectResponse(){
		$this->getResponse()
			->setHeader('HTTP/1.1', '403 Session Expired')
			->setHeader('Login-Required', 'true')
			->sendResponse();
		return $this;
	}
	
	/**
	 * Validate ajax request and redirect on failure
	 *
	 * @return bool
	 */
	protected function _expireAjax(){
		
		if (!$this->getRequest()->isAjax()){
			$this->_redirectUrl(Mage::getBaseUrl('link', true));
			return;
		}
		
		if (!$this->getOnepage()->getQuote()->hasItems() || $this->getOnepage()->getQuote()->getHasError() || $this->getOnepage()->getQuote()->getIsMultiShipping()) {
			$this->_ajaxRedirectResponse();
			return true;
		}
		
		$action = $this->getRequest()->getActionName();
		if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true) && !in_array($action, array('index', 'progress'))) {
				$this->_ajaxRedirectResponse();
				return true;
		}
	
		return false;
	}

	/**
	 * Get shipping method step html
	 *
	 * @return string
	 */
	protected function _getShippingMethodsHtml(){
		$layout = $this->getLayout();
		$update = $layout->getUpdate();
		$update->load('checkout_onepage_index');
		$layout->generateXml();
		$layout->generateBlocks();
		$shippingMethods = $layout->getBlock('checkout.onepage.shipping_method');
		$shippingMethods->setTemplate('opc/onepage/shipping_method.phtml');
		return $shippingMethods->toHtml();
	}
	
	/**
	 * Get payments method step html
	 *
	 * @return string
	 */
	protected function _getPaymentMethodsHtml($use_method = false, $just_save = false){
	
		/** UPDATE PAYMENT METHOD **/
		if($use_method && $use_method != -1)
			$apply_method = $use_method;
		else
		{
			if($use_method == -1)
				$apply_method = Mage::getStoreConfig(self::XML_PATH_DEFAULT_PAYMENT);
			else
			{
				$apply_method = Mage::helper('opc')->getSelectedPaymentMethod();
				if(empty($apply_method))
					$apply_method = Mage::getStoreConfig(self::XML_PATH_DEFAULT_PAYMENT);
			}
		}

		$_cart = $this->_getCart();
		$_quote = $_cart->getQuote();
Mage::log('_getPaymentMethodsHtml = '. print_r($apply_method, true), null, 'system.log', true);		
		$_quote->getPayment()->setMethod($apply_method);
		$_quote->setTotalsCollectedFlag(false)->collectTotals();
		$_quote->save();

	}

	/**
	 * Get review step html
	 *
	 * @return string
	 */
	protected function _getReviewHtml(){
		
		//clear cache
		Mage::app()->getCacheInstance()->cleanType('layout');
		
		$layout = $this->getLayout();
		$update = $layout->getUpdate();
		$update->load('checkout_onepage_review');
		$layout->generateXml();
		$layout->generateBlocks();
		$review = $layout->getBlock('root');
		$review->setTemplate('opc/onepage/review/info.phtml');
		
		//clear cache
		Mage::app()->getCacheInstance()->cleanType('layout');
		return $review->toHtml();
	}
	
	
	private function checkNewslatter(){
			Mage::getSingleton('core/session')->unsIsSubscribed();
	}

	private function _countDots($subDomainName) {
		if (preg_match('/[\'^£$%&*()} {@#~?><>,.|=_+¬-]/', $subDomainName)){
			return true;
		}
		else if ($this->_countLength($subDomainName)) {
			return true;
		}
		return false;
	}
	
	
	private function _countLength($subDomainName){
		if ((strlen($subDomainName) < 5) || (strlen($subDomainName) > 10)){
			return true;
		} 
		return false;
	
	}
	
	
	public function checkWebsiteAction() {

		if ($this->_expireAjax()) {
			return;
		}
		
		if ($this->getRequest()->isPost()) {	
			$data = $this->getRequest()->getPost();

			$customerCollection = Mage::getModel('customer/customer')->getCollection()
				->addAttributeToFilter('username', $data['username']);

			if(Mage::getSingleton('customer/session')->isLoggedIn())
			{
				$customerObject = Mage::getSingleton('customer/session')->getCustomer();
				$customerCollection->addAttributeToFilter('entity_id', array('neq' => $customerObject->getId()));
			}
			
			$result['error'] = false;
			$result['message'] = $this->__('<i class="fa fa-check" aria-hidden="true"></i> Available');
			
			if($customerCollection->count())
			{
				$result['error'] = true;
				$result['message'] = $this->__('Not Available');
			}
			else if (is_numeric($data['username'][0])) {
				$result['error'] = true;
				$result['message'] = $this->__('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Your site name may only start from alphabetic character.');
			}
			else if ($this->_countDots($data['username'])){
				$result['error'] = true;
				$result['message'] = $this->__('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Your site name may contain 5-10 alpha/numeric characters only.');
			}
			else if (empty(trim($data['username']))){
				$result['error'] = true;
				$result['message'] = $this->__('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Your site name may only start alphabetic character..');
			}
			else
				Mage::getSingleton('core/session')->setAmbassadorWebsiteName($data['username']);

			$this->getResponse()->setHeader('Content-type','application/json', true);
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}

	public function saveProfileAction() {

		if ($this->_expireAjax()) {
			return;
		}
		
		if ($this->getRequest()->isPost()) {	
			$data = $this->getRequest()->getPost('profile', array());

			Mage::getSingleton('core/session')->setAmbassadorProfileInfo($data);

			$this->getResponse()->setHeader('Content-type','application/json', true);
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array()));
		}
	}

	public function saveStarterKitAction() {
		if ($this->_expireAjax()) {
			return;
		}
		
		if ($this->getRequest()->isPost()) {	
			$productId = $this->getRequest()->getPost('starterkit');

			$totals_before = $this->_getSession()->getQuote()->getGrandTotal();
			$methods_before = Mage::helper('opc')->getAvailablePaymentMethods();

			if(isset($productId))
			{
				Mage::getSingleton('checkout/cart')->truncate();
				
				$cartHelper = Mage::helper('checkout/cart');
				
				$product = Mage::getModel('catalog/product')->load($productId);
		        $cartHelper->getCart()->addProduct($product, array('qty' => 1))->save();
			}

			$result = array();

			$result['shipping'] = $this->_getShippingMethodsHtml();

			$methods_after = Mage::helper('opc')->getAvailablePaymentMethods();
			$use_method = Mage::helper('opc')->checkUpdatedPaymentMethods($methods_before, $methods_after);

			if($use_method != -1)
			{
				if(empty($use_method))
					$use_method = -1;

				$result['payments'] = $this->_getPaymentMethodsHtml($use_method, true);
				$result['reload_payments'] = true; 
			}

			$totals_after = $this->_getSession()->getQuote()->getGrandTotal();

			if($totals_before != $totals_after)
				$result['reload_totals'] = true;

			$this->getResponse()->setHeader('Content-type','application/json', true);
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array()));
		}
	}

	public function saveGeneralAction($data) {
			Mage::getSingleton('core/session')->setGeneralData($data);
	}
	
	/*
     * // STEP(1)
     * $checkout->saveCheckoutMethod('guest');
     * // STEP(2)
     * $checkout->saveBilling($billingAddress, false);
     * 
	 */
	public function saveBillingAction($data){
		
		if (count($data) > 0) {
			
						
			# STEP(1)
			$this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER);

			
			#$customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
			#$customerAddressId = $data['address_id'];
			$customerAddressId = false;
			
			if (isset($data['email'])) {
				$data['email'] = trim($data['email']);
			}

			// get grand totals before
			$totals_before = $this->_getSession()->getQuote()->getGrandTotal();
			
			/// get list of available methods before billing changes
			$methods_before = Mage::helper('opc')->getAvailablePaymentMethods();
			///////
			# STEP(2)
			$result = $this->getOnepage()->saveBilling($data, $customerAddressId);
	
			if (!isset($result['error'])) {
				/* check quote for virtual */
				if ($this->getOnepage()->getQuote()->isVirtual()) {
					$result['isVirtual'] = true;
				};

// 				//load shipping methods block if shipping as billing;
// 				Mage::dispatchEvent('opc_saveGiftMessage', array(
// 					'request'=>"No Gift",
// 					'quote'=>$this->getOnepage()->getQuote(),
// 				));


				/// get list of available methods after discount changes
				$methods_after = Mage::helper('opc')->getAvailablePaymentMethods();
				///////
				
				// check if need to reload payment methods
				$use_method = Mage::helper('opc')->checkUpdatedPaymentMethods($methods_before, $methods_after);


				// get grand totals after
				$totals_after = $this->_getSession()->getQuote()->getGrandTotal();
				
				
			}else{
				
				echo "<br>No * data found". $result['message'];
				exit();
			}
		}else {
			echo "2 No data found";
			exit();
		}
	}
	
	
	/**
	 * Shipping save action
	 */
	public function saveShippingAction($data){
	
		//TODO create response if post not exist
		$responseData = array();
	
		$result = array();

		// get grand totals after
		$totals_before = $this->_getSession()->getQuote()->getGrandTotal();

		#$data = $this->getRequest()->getPost('shipping', array());
		
		#$customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
		$customerAddressId = false;
		# // STEP(3)
		# $checkout->saveShipping($shippingAddress, false);
		$result = $this->getOnepage()->saveShipping($data, $customerAddressId);

		if (isset($result['error'])){
			$responseData['error'] = true;
			$responseData['message'] = $result['message'];
			$responseData['messageBlock'] = 'shipping';
			
			echo "<br>saveShippingAction data found". $result['message'];
			exit();
		}else{
			Mage::dispatchEvent('opc_saveGiftMessage', array(
				'request' => $this->getRequest(),
				'quote' => $this->getOnepage()->getQuote(),
			));

			// get grand totals after
			$totals_after = $this->_getSession()->getQuote()->getGrandTotal();
			
		}

	
	}
	
	/**
	 * reload available shipping methods based on address
	 */
	
	
	/**
	 * Shipping method save action
	 */
	public function saveShippingMethodAction($data){
		
		$data = "tablerate_bestway";
Mage::log('saveShippingMethod = '. print_r($data, true), null, 'system.log', true);			
			# // STEP(4)
			# $checkout->saveShippingMethod('flatrate_flatrate');
			$result = $this->getOnepage()->saveShippingMethod($data);
			
			
	echo "<pre>";
	print_r($result);
	echo "</pre>";
						
			/*
			 $result will have erro data if shipping method is empty
			*/
			if(!$result) {
#Mage::log('saveShippingMethod 2 = '. print_r($this->getRequest(), true), null, 'system.log', true);	
			
				Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
											array('request'=>$this->getRequest(),
											'quote'=>$this->getOnepage()->getQuote())
									);
				
				#$this->getOnepage()->getQuote()->collectTotals();
			}
			#$this->getOnepage()->getQuote()->collectTotals()->save();
	}
	
	public function reviewAction(){
		if ($this->_expireAjax()) {
			return;
		}
		$responseData = array();
		$responseData['review'] = $this->_getReviewHtml();
		$responseData['grandTotal'] = Mage::helper('opc')->getGrandTotal();
		$this->getResponse()->setHeader('Content-type','application/json', true);
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseData));
	}
	
	
	public function paymentsAction(){
		$this->_getPaymentMethodsHtml();
	}
	
	
	public function savePaymentAction($data)
	{
        
		try {
	
			// set payment to quote
			$result = array();
			if(isset($data['cc_number']))
			{
				$data['cc_number'] = str_replace(' ', '', $data['cc_number']);
			}
			# // STEP(5)
			# $checkout->savePayment(array('method'=>'checkmo'));
			$result = $this->getOnepage()->savePayment($data);
	
			// get section and redirect data
			$redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
			if (empty($result['error'])) {
				
				echo "<pre>";
				print_r($result);
				echo "</pre>";
				
				$this->loadLayout('checkout_onepage_review');
				$result['review'] = $this->_getReviewHtml();
				$result['grandTotal'] = Mage::helper('opc')->getGrandTotal();
			}
			if ($redirectUrl) {
				$result['redirect'] = $redirectUrl;
			}
		} catch (Mage_Payment_Exception $e) {
Mage::log('savePaymentAction  error 1  = '.print_r($e->getMessage(), true), null, 'system.log', true);
			if ($e->getFields()) {
				$result['fields'] = $e->getFields();
			}
			$result['error'] = $e->getMessage();
		} catch (Mage_Core_Exception $e) {
Mage::log('savePaymentAction  error 2  = '.print_r($e->getMessage(), true), null, 'system.log', true);			
			$result['error'] = $e->getMessage();
		} catch (Exception $e) {
Mage::log('savePaymentAction  error 3  = '.print_r($e->getMessage(), true), null, 'system.log', true);			
			Mage::logException($e);
			$result['error'] = $this->__('Unable to set Payment Method.');
		}
		

	}
	
	
	
	/**
	* Create order action
	* STEP(6)
	*/
	public function saveOrderAction($data){
	
		$version = Mage::getVersionInfo();
	
		$result = array();
		try {
			
	
			#$data = $this->getRequest()->getPost('payment', false);
Mage::log('saveOrderAction  payment = '.print_r($data, true), null, 'system.log', true);		
			if ($data) {
				
				$quote = $this->getOnepage()->getQuote();
				if ($quote->isVirtual()) {
					$quote->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
				} else {
					$quote->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
				}
				
				// shipping totals may be affected by payment method
				if (!$quote->isVirtual() && $quote->getShippingAddress()) {
					$quote->getShippingAddress()->setCollectShippingRates(true);
				}
				
				
				/** Magento CE 1.8 version**/
				if ($version['minor'] == 8){
					
					$data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
					| Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
					| Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
					| Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
					| Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
					
				}
				$this->getOnepage()->getQuote()->getPayment()->importData($data);
			}
	

			# // STEP(6)
			# $checkout->saveOrder() returns array holding empty object of type Mage_Checkout_Model_Type_Onepage
			$getModel = Mage::getModel("Mage_Checkout_Model_Type_Onepage")->saveOrder();
			#$this->getOnepage()->saveOrder();
			
			
			$redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
			
		} catch (Mage_Payment_Model_Info_Exception $e) {
Mage::log('saveOrder  error 1  = '.print_r($e->getMessage(), true), null, 'system.log', true);			
			$message = $e->getMessage();
			
			if (!empty($message)) {
				$result['error'] = $message;
			}
			
		
		} catch (Mage_Core_Exception $e) {
Mage::log('saveOrder  error 2  = '.print_r($e->getMessage(), true), null, 'system.log', true);			
			Mage::logException($e);
			
			Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
			
			$result['error'] = $e->getMessage();
	
			$gotoSection = $this->getOnepage()->getCheckout()->getGotoSection();
			if ($gotoSection) {
				$this->getOnepage()->getCheckout()->setGotoSection(null);
			}
			
			$updateSection = $this->getOnepage()->getCheckout()->getUpdateSection();
			
			if ($updateSection) {				
				$this->getOnepage()->getCheckout()->setUpdateSection(null);
			}
		} catch (Exception $e) {
Mage::log('saveOrder  error 3  = '.print_r($e->getMessage(), true), null, 'system.log', true);			
			Mage::logException($e);
			Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
			$result['error'] = $this->__('There was an error processing your order. Please contact us or try again later.');
		}		
# setRecurringPaymentProfiles		
		$this->getOnepage()->getQuote()->save();
		
		/**
		 * when there is redirect to third party, we don't want to save order yet.
		 * we will save the order in return action.
		*/
		if (isset($redirectUrl) && !empty($redirectUrl)) {
			Mage::log('Not success', null, 'system.log', true);	
		}else{
			Mage::log(' success', null, 'system.log', true);
		}
	}

	
	/** TODO MOVE TO CUSTOMER CONTROLLER **/
	protected function _getSessionCustomer(){
		return Mage::getSingleton('customer/session');
	}
	
	public function forgotpasswordAction(){
		$response = array();
		$email = (string) $this->getRequest()->getPost('email');
	
		if ($email) {
			if (!Zend_Validate::is($email, 'EmailAddress')) {
				$this->_getSessionCustomer()->setForgottenEmail($email);
	
				$response['error'] = 1;
				$response['message'] = $this->__('Invalid email address.');
				$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
				return;
			}
	
			/** @var $customer Mage_Customer_Model_Customer */
			$customer = Mage::getModel('customer/customer')
					->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
					->loadByEmail($email);
	
			if ($customer->getId()) {
				try {
					$newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
					$customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
					$customer->sendPasswordResetConfirmationEmail();
				} catch (Exception $exception) {
						
					$response['error'] = 1;
					$response['message'] = $exception->getMessage();
					$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
	
					return;
				}
			}
			$response['message']  = Mage::helper('customer')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('customer')->htmlEscape($email));
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
			return;
		} else {
				
				
			$response['error'] = 1;
			$response['message'] = $this->__('Please enter your email.');
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
				
			return;
		}
	}
	
	public function commentAction(){
		if ($this->_expireAjax()) {
			return;
		}
		$comment  = $this->getRequest()->getParam('comment');
		if (!empty($comment)){
			Mage::getSingleton('core/session')->setOpcOrderComment($comment);
		}else{
			Mage::getSingleton('core/session')->unsOpcOrderComment($comment);
		} 
		return;
	}

	protected function initDefaultAddress()
	{
		$billing_address = $this->getOnepage()->getQuote()->getBillingAddress();

		$bill_country = $billing_address->getCountryId();
		
		if(!empty($bill_country))
			return $this;
		
		$countryId = Mage::helper('core')->getDefaultCountry();
		$ip_country =  Mage::getStoreConfig(self::XML_PATH_GEO_COUNTRY) ? Mage::helper('opc/country')->get() : $countryId;
		$countryId =  !empty($ip_country)?$ip_country:$countryId;
		$ip_city =  Mage::getStoreConfig(self::XML_PATH_GEO_CITY) ? Mage::helper('opc/city')->get() : false;
	
		$default_billing_addr	= array(
				'country_id'   => $countryId,
				'city'      => !empty($ip_city)?$ip_city:null,
		);
	
		$billing_address->addData($default_billing_addr);
		$billing_address->implodeStreetAddress();
		
		if (!$this->getOnepage()->getQuote()->isVirtual())
		{
			// set shipping address same as billing
			$bill = clone $billing_address;
			$bill->unsAddressId()->unsAddressType();
			$ship = $this->getOnepage()->getQuote()->getShippingAddress();
			$ship_method = $ship->getShippingMethod();
			$ship->addData($bill->getData());
			$ship->setSameAsBilling(1)->setShippingMethod($ship_method)->setCollectShippingRates(true);
		}
	
		$this->getOnepage()->getQuote()->collectTotals()->save();
	
		return $this;
	}
	
	protected function updateDefaultPayment(){
		$defaultPaymentMethod = Mage::getStoreConfig(self::XML_PATH_DEFAULT_PAYMENT);
		$_cart = $this->_getCart();
		$_quote = $_cart->getQuote();
		$_quote->getPayment()->setMethod($defaultPaymentMethod);
		$_quote->setTotalsCollectedFlag(false)->collectTotals();
		$_quote->save();
	}
}