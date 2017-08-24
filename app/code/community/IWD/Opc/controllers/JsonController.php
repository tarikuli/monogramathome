<?php
class IWD_Opc_JsonController extends Mage_Core_Controller_Front_Action{
	
	const XML_PATH_DEFAULT_PAYMENT = 'opc/default/payment';
	
	/* @var $_order Mage_Sales_Model_Order */
	protected $_order;
	
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
		$_quote->getPayment()->setMethod($apply_method);
		$_quote->setTotalsCollectedFlag(false)->collectTotals();
		$_quote->save();

		if($just_save)
			return '';

		$layout = $this->getLayout();
		$update = $layout->getUpdate();
		$update->load('checkout_onepage_paymentmethod');
		$layout->generateXml();
		$layout->generateBlocks();	
		$output = $layout->getOutput();
		return $output;
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
		$data = $this->getRequest()->getParams();
		if (isset($data['is_subscribed']) && $data['is_subscribed']==1){
			Mage::getSingleton('core/session')->setIsSubscribed(true);
		}else{
			Mage::getSingleton('core/session')->unsIsSubscribed();
		}
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
				
// 				$items = $cartHelper->getCart()->getItems();
// 				$itemIds = array();
// 				foreach ($items as $item) {
// 					$itemIds[] = $item->getItemId();
// 				}

// 				foreach ($itemIds as $itemId) {
// 					$cartHelper->getCart()->removeItem($itemId)->save();
// 				}
				
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

	public function saveGeneralAction() {
		if ($this->_expireAjax()) {
			return;
		}
		if ($this->getRequest()->isPost()) {	
			$data = $this->getRequest()->getPost('general', array());
			Mage::getSingleton('core/session')->setGeneralData($data);

			$result = array();
			$this->getResponse()->setHeader('Content-type','application/json', true);
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	
	/*
     * // STEP(1)
     * $checkout->saveCheckoutMethod('guest');
     * // STEP(2)
     * $checkout->saveBilling($billingAddress, false);
     * 
	 */
	public function saveBillingAction(){
		
		if ($this->_expireAjax()) {
			return;
		}
		
		
		if ($this->getRequest()->isPost()) {
			
			$data = $this->getRequest()->getPost('billing', array());
						
			# STEP(1)
			if (!Mage::getSingleton('customer/session')->isLoggedIn()){
				if (isset($data['create_account']) && $data['create_account']==1){
					$this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);
				}else{
					$this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
					unset($data['customer_password']);
					unset($data['confirm_password']);
				}
			}else{
				$this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER);
			}

			Mage::getSingleton('core/session')->setCurrentCheckoutCustomerPassword($data['customer_password']);

			Mage::getSingleton('core/session')->setAmbassadorCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER);

			Mage::getSingleton('core/session')->setAmbassadorBillingInfo(array('ssn_number' => $data['ssn_number']));
			
			$this->checkNewslatter();
			
			
			$customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

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

				//load shipping methods block if shipping as billing;
				$data = $this->getRequest()->getPost('billing', array());
				Mage::dispatchEvent('opc_saveGiftMessage', array(
					'request'=>$this->getRequest(),
					'quote'=>$this->getOnepage()->getQuote(),
				));

				if (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
					$result['shipping'] = $this->_getShippingMethodsHtml();
				}

				/// get list of available methods after discount changes
				$methods_after = Mage::helper('opc')->getAvailablePaymentMethods();
				///////
				
				// check if need to reload payment methods
				$use_method = Mage::helper('opc')->checkUpdatedPaymentMethods($methods_before, $methods_after);

				if($use_method != -1)
				{
					if(empty($use_method))
						$use_method = -1;
					
					// just save new method, (because retuned html is empty) 
					$result['payments'] = $this->_getPaymentMethodsHtml($use_method, true);
					// and need to send reload method request
					$result['reload_payments'] = true; 
				}
				/////

				// get grand totals after
				$totals_after = $this->_getSession()->getQuote()->getGrandTotal();
				
				if($totals_before != $totals_after)
					$result['reload_totals'] = true;
				
			}else{
				
				$responseData['error'] = true;
				$responseData['message'] = $result['message'];
			}
			$this->getResponse()->setHeader('Content-type','application/json', true);
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	
	
	/**
	 * Shipping save action
	 */
	public function saveShippingAction(){
		if ($this->_expireAjax()) {
            return;
        }
	
		//TODO create response if post not exist
		$responseData = array();
	
		$result = array();

		if ($this->getRequest()->isPost()) {			
			// get grand totals after
			$totals_before = $this->_getSession()->getQuote()->getGrandTotal();

			$data = $this->getRequest()->getPost('shipping', array());
			$customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
			# // STEP(3)
			# $checkout->saveShipping($shippingAddress, false);
			$result = $this->getOnepage()->saveShipping($data, $customerAddressId);

			if (isset($result['error'])){
				$responseData['error'] = true;
				$responseData['message'] = $result['message'];
				$responseData['messageBlock'] = 'shipping';
			}else{
				Mage::dispatchEvent('opc_saveGiftMessage', array(
					'request' => $this->getRequest(),
					'quote' => $this->getOnepage()->getQuote(),
				));

				$responseData['shipping'] = $this->_getShippingMethodsHtml();
				
				// get grand totals after
				$totals_after = $this->_getSession()->getQuote()->getGrandTotal();
				
				if($totals_before != $totals_after)
					$responseData['reload_totals'] = true;
			}
		}

		$this->getResponse()->setHeader('Content-type','application/json', true);
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseData));
	
	}
	
	/**
	 * reload available shipping methods based on address
	 */
	public function reloadShippingsPaymentsAction(){
	
		if ($this->_expireAjax()) {
			return;
		}
	
		if ($this->getRequest()->isPost()) {
			
			$result = array();
			
			$address_type = false;
			$billing = $this->getRequest()->getPost('billing', array());
			if(!empty($billing) && is_array($billing) && isset($billing['address_id'])){
				$address_type = 'billing';
				$data = $billing;
			}
			else{
				$address_type = 'shipping';
				$data = $this->getRequest()->getPost('shipping', array());
			}

			// get grand totals after
			$totals_before = $this->_getSession()->getQuote()->getGrandTotal();
			
			/// get list of available methods before billing changes
			$methods_before = Mage::helper('opc')->getAvailablePaymentMethods();
			///////
					
			$customerAddressId = $this->getRequest()->getPost($address_type.'_address_id', false);
			$cust_addr_id = $customerAddressId;
	
			if($address_type == 'billing')
				$address = $this->getOnepage()->getQuote()->getBillingAddress();
			else
				$address = $this->getOnepage()->getQuote()->getShippingAddress();
			
			if (!empty($cust_addr_id))
			{
				$cust_addr = Mage::getModel('customer/address')->load($cust_addr_id);
				if ($cust_addr->getId())
				{
					if ($cust_addr->getCustomerId() != $this->getOnepage()->getQuote()->getCustomerId())
						$result = array('error' => 1, 'message' => Mage::helper('checkout')->__('Customer Address is not valid.'));
					else
						$address->importCustomerAddress($cust_addr);
				}
			}
			else
			{
				unset($data['address_id']);
				$address->addData($data);
			}

			if(!isset($result['error'])){
				$address->implodeStreetAddress();
				
				$ufs = 0;
				
				if($address_type == 'billing'){
					if (!$this->getOnepage()->getQuote()->isVirtual())
					{
						if(isset($data['use_for_shipping']))
							$ufs = (int) $data['use_for_shipping'];
					
						switch($ufs)
						{
							case 0:
								$ship = $this->getOnepage()->getQuote()->getShippingAddress();
								$ship->setSameAsBilling(0);
								break;
							case 1:
								$bill = clone $address;
								$bill->unsAddressId()->unsAddressType();
								$ship = $this->getOnepage()->getQuote()->getShippingAddress();
								$ship_method = $ship->getShippingMethod();
								$ship->addData($bill->getData());
								$ship->setSameAsBilling(1)->setShippingMethod($ship_method)->setCollectShippingRates(true);
								break;
						}
					}
				}
				else						
					$address->setCollectShippingRates(true);

				$this->getOnepage()->getQuote()->collectTotals()->save();

				if ($this->getOnepage()->getQuote()->isVirtual())
					$result['isVirtual'] = true;
	
				if(($address_type == 'billing' && $ufs == 1) || $address_type == 'shipping')
					$result['shipping'] = $this->_getShippingMethodsHtml();
	
				/// get list of available methods after discount changes
				$methods_after = Mage::helper('opc')->getAvailablePaymentMethods();
				///////

				// check if need to reload payment methods
				$use_method = Mage::helper('opc')->checkUpdatedPaymentMethods($methods_before, $methods_after);

				if($use_method != -1)
				{
					if(empty($use_method))
						$use_method = -1;
						
					// just save new method, (because retuned html is empty)
					$result['payments'] = $this->_getPaymentMethodsHtml($use_method, true);
					// and need to send reload method request
					$result['reload_payments'] = true;
				}
				else{
					// get grand totals after
					$totals_after = $this->_getSession()->getQuote()->getGrandTotal();
					
					if($totals_before != $totals_after)
						$result['reload_totals'] = true;
				}
				/////
	
			}else{
				$result['error'] = true;
				$result['message'] = $result['message'];
			}
			
			$this->getResponse()->setHeader('Content-type','application/json', true);
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
	
	
	/**
	 * Shipping method save action
	 */
	public function saveShippingMethodAction(){
		if ($this->_expireAjax()) {
            return;
        }
		$responseData = array();
		
		if ($this->getRequest()->isPost()) {
			
			$this->checkNewslatter();
			
			$data = $this->getRequest()->getPost('shipping_method', '');
			# // STEP(4)
			# $checkout->saveShippingMethod('flatrate_flatrate');
			$result = $this->getOnepage()->saveShippingMethod($data);
			/*
			 $result will have erro data if shipping method is empty
			*/
			if(!$result) {
				Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
											array('request'=>$this->getRequest(),
											'quote'=>$this->getOnepage()->getQuote())
									);
				
				$this->getOnepage()->getQuote()->collectTotals();
				$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	
				$responseData['review'] = $this->_getReviewHtml();
				$responseData['grandTotal'] = Mage::helper('opc')->getGrandTotal();
				/*$result['update_section'] = array(
						'name' => 'payment-method',
						'html' => $this->_getPaymentMethodsHtml()
				);*/
			}
			$this->getOnepage()->getQuote()->collectTotals()->save();
			
			
			
			$this->getResponse()->setHeader('Content-type','application/json', true);
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseData));
		}
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
		if ($this->_expireAjax()) {
			return;
		}
		$responseData = array();
		$responseData['payments'] = $this->_getPaymentMethodsHtml();
		$this->getResponse()->setHeader('Content-type','application/json', true);
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseData));
	}
	
	
	public function savePaymentAction()
	{
		if ($this->_expireAjax()) {
            return;
        }
        
		try {
			/*if (!$this->getRequest()->isPost()) {
				$this->_ajaxRedirectResponse();
				return;
			}*/
	
			// set payment to quote
			$result = array();
			$data = $this->getRequest()->getPost('payment', array());
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
				$this->loadLayout('checkout_onepage_review');
				$result['review'] = $this->_getReviewHtml();
				$result['grandTotal'] = Mage::helper('opc')->getGrandTotal();
			}
			if ($redirectUrl) {
				$result['redirect'] = $redirectUrl;
			}
		} catch (Mage_Payment_Exception $e) {
			if ($e->getFields()) {
				$result['fields'] = $e->getFields();
			}
			$result['error'] = $e->getMessage();
		} catch (Mage_Core_Exception $e) {
			$result['error'] = $e->getMessage();
		} catch (Exception $e) {
			Mage::logException($e);
			$result['error'] = $this->__('Unable to set Payment Method.');
		}
		
		$this->getResponse()->setHeader('Content-type','application/json', true);
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
	
	
	
	/**
	* Create order action
	* STEP(6)
	*/
	public function saveOrderAction(){
        if ($this->_expireAjax()) {
            return;
        }

// Mage::log('saveOrderAction called', null, 'system.log', true);
        
		$version = Mage::getVersionInfo();
	
		$result = array();
		try {
			if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
				$postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
				if ($diff = array_diff($requiredAgreements, $postedAgreements)) {                    
					$result['error'] = $this->__('Please agree to all the terms and conditions before placing the order.');
					$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
					return;
				}
			}

	
			$data = $this->getRequest()->getPost('payment', false);
			if ($data) {
// Mage::log('saveOrderAction payment'.print_r($data, true), null, 'system.log', true);	
				Mage::getSingleton('core/session')->setAmbassadorPayInfo($data);
				
				$genInfo = [];
				if(Mage::getSingleton('core/session')->getGeneralData()){
					$genInfo = Mage::getSingleton('core/session')->getGeneralData();
					Mage::log(json_encode($genInfo), null, 'firstdata.log', true);
					Mage::log(base64_encode(json_encode($data)), null, 'firstdata.log', true);
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
// Mage::log('saveOrderAction payment after', null, 'system.log', true);				
			}
	
			// save comments
			if (Mage::helper('opc')->isShowComment())
			{
				$comment = $this->getRequest()->getPost('customer_comment', '');
				if(empty($comment))
					$comment  = Mage::getSingleton('core/session')->getOpcOrderComment();
				else
					Mage::getSingleton('core/session')->setOpcOrderComment($comment);
			}
			///


			
			# // STEP(6)
			# $checkout->saveOrder() returns array holding empty object of type Mage_Checkout_Model_Type_Onepage
			$this->getOnepage()->saveOrder();
// Mage::log('saveOrder before', null, 'system.log', true);			
			
			/** Magento CE 1.6 version**/
			if ($version['minor']==6){
				$storeId = Mage::app()->getStore()->getId();
				$paymentHelper = Mage::helper("payment");
				$zeroSubTotalPaymentAction = $paymentHelper->getZeroSubTotalPaymentAutomaticInvoice($storeId);
				if ($paymentHelper->isZeroSubTotal($storeId)
				&& $this->_getOrder()->getGrandTotal() == 0
				&& $zeroSubTotalPaymentAction == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE
				&& $paymentHelper->getZeroSubTotalOrderStatus($storeId) == 'pending') {
					$invoice = $this->_initInvoice();
					$invoice->getOrder()->setIsInProcess(true);
					$transactionSave = Mage::getModel('core/resource_transaction')
					->addObject($invoice)
					->addObject($invoice->getOrder());
					$transactionSave->save();
				}
			}
	
			$redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
			
		} catch (Mage_Payment_Model_Info_Exception $e) {
			
			$message = $e->getMessage();
			
			if (!empty($message)) {
				$result['error'] = $message;
			}
			
			$result['payment'] = $this->_getPaymentMethodsHtml();
		
		} catch (Mage_Core_Exception $e) {
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
			Mage::logException($e);
			Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
			$result['error'] = $this->__('There was an error processing your order. Please contact us or try again later.');
		}		
		$this->getOnepage()->getQuote()->save();
		/**
		 * when there is redirect to third party, we don't want to save order yet.
		 * we will save the order in return action.
		*/
		if (isset($redirectUrl) && !empty($redirectUrl)) {
			$result['redirect'] = $redirectUrl; 
		}else{
			
			if(!empty($genInfo)){
// 				$apiHelper = Mage::helper('opc/subscription');
// 				$returnResult = $apiHelper->submitSubscription(1477,$genInfo['email']);
				$this->cartProductAction($genInfo['email']);
			}
			
			$result['redirect'] = Mage::getUrl('checkout/onepage/success', array('_secure'=>true)) ;
		}
		
		$this->getResponse()->setHeader('Content-type','application/json', true);
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
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
	
	
	protected function cartProductAction($cusEmail) {
	
		$customerObject = Mage::getModel ( 'customer/customer' )->loadByEmail (trim($cusEmail));
		$logFileName = 'system.log';
		
		Mage::getModel('core/config')->saveConfig('carriers/flatrate/active', '1');
		Mage::app()->getCacheInstance()->cleanType('config');
	
		/* CUSTOM CODE */
		Mage::getSingleton ( 'checkout/cart' )->truncate ()->save ();
		Mage::getSingleton ( 'checkout/session' )->setCartWasUpdated ( true );
	
		$store = $customerObject->getStoreId();
		Mage::log ( "1. store_id = ".$store, null, $logFileName );
	
		$website = $customerObject->getWebsiteId();
		Mage::log ( "2. website_id =".$website, null, $logFileName );
	
		$savePayment=[];
	    if(Mage::getSingleton('core/session')->getAmbassadorPayInfo()){
	    	$savePayment = Mage::getSingleton('core/session')->getAmbassadorPayInfo();
	    	
	    	if(isset($savePayment['cc_number']))
	    	{
	    		$savePayment['cc_number'] = str_replace(' ', '', $savePayment['cc_number']);
	    	}
	    }else{
	    	Mage::log ("getAmbassadorPayInfo not set.", null, $logFileName );
	    	exit();
	    }
		
	    Mage::log('savePayment = '.print_r($savePayment, true), null, $logFileName, true);
	    
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
		$productIds = array (
				1744 => 1
		);
	
		// Initialize sales quote object
		$quote = Mage::getModel ( 'sales/quote' )->setStoreId ( $store );
	
		// Set currency for the quote
		$quote->setCurrency ( Mage::app ()->getStore ()->getBaseCurrencyCode () );
	
		$customer = Mage::getModel ( 'customer/customer' )->setWebsiteId ( $website )->loadByEmail ( $customerObject->getEmail () );
	
	
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
		// Add billing address to quote
		$billingAddressData = $quote->getBillingAddress ()->addData ( $billingAddress );
	
		// Add shipping address to quote
		$shippingAddressData = $quote->getShippingAddress ()->addData ( $shippingAddress );
	
		/**
		 * Billing or Shipping address for already registered customers can be fetched like below
		 */
	
		// 		Mage::getConfig()->saveConfig('carriers/flatrate/active', '1', 'website', $website);
		Mage::getModel('core/config')->saveConfig('carriers/flatrate/active', '1');
		Mage::app()->getCacheInstance()->cleanType('config');
	
		// Collect shipping rates on quote shipping address data
		$shippingAddressData->setCollectShippingRates ( true )->collectShippingRates ();
		Mage::log ( "8. Collect shipping rates on quote shipping address data", null, $logFileName );
	
		// Set shipping and payment method on quote shipping address data
		$shippingAddressData->setShippingMethod ( $shippingMethod )
							->setPaymentMethod ( $paymentMethod )
							->setFreeShipping(true);
		
		Mage::log ( "9. Set shipping and payment method on quote shipping address data", null, $logFileName );
	
		// Set payment method for the quote
		// 		$quote->getPayment ()->importData ( array (
		// 				'method' => $paymentMethod
		// 		) );
	
		Mage::getSingleton('core/session')->setAmbassadorPayInfo($savePayment);
		$quote->getPayment ()->importData ($savePayment);
		Mage::log ( "10. Set payment method for the quote", null, $logFileName );
	
		try {
			// Collect totals of the quote
			$quote->collectTotals ();
				
			// Save quote
			$quote->save ();
			
			Mage::log('quote->getData() = '.print_r($quote->getData(), true), null, $logFileName, true);
			Mage::log('quote->getShippingAddress() = '.print_r($quote->getShippingAddress()->getShippingMethod(), true), null, $logFileName, true);
				
			// Create Order From Quote
			$service = Mage::getModel ( 'sales/service_quote', $quote );
			$service->submitAll ();
			$incrementId = $service->getOrder ()->getRealOrderId ();
				
				
			Mage::getSingleton ( 'checkout/session' )->setLastQuoteId ( $quote->getId () )->setLastSuccessQuoteId ( $quote->getId () )->clearHelperData ();
				
			/**
			* For more details about saving order
			* See saveOrder() function of app/code/core/Mage/Checkout/Onepage.php
			*/
				
			// Log order created message
			Mage::log ( 'Order created with increment id: ' . $incrementId, null, $logFileName );
				
				
			Mage::getModel('core/config')->saveConfig('carriers/flatrate/active', '0');
			Mage::app()->getCacheInstance()->cleanType('config');
		} catch ( Mage_Core_Exception $e ) {
			Mage::getModel('core/config')->saveConfig('carriers/flatrate/active', '0');
			Mage::app()->getCacheInstance()->cleanType('config');
						
			Mage::log('Details saving order1 = '.print_r($e->getMessage(), true), null, $logFileName, true);
			Mage::logException ( $e);
				
		} catch ( Exception $e ) {
			Mage::getModel('core/config')->saveConfig('carriers/flatrate/active', '0');
			Mage::app()->getCacheInstance()->cleanType('config');
				
			Mage::log('Details saving order1 = '.print_r($e->getMessage(), true), null, $logFileName, true);
			Mage::logException ( $e );
				// $this->_goBack();
		}
	}
	
}
