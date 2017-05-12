<?php
class IWD_Opc_IndexController extends Mage_Checkout_Controller_Action{
	
	const XML_PATH_TITLE = 'opc/global/title';
	
	const XML_PATH_DEFAULT_PAYMENT = 'opc/default/payment';
	
	const XML_PATH_GEO_COUNTRY = 'opc/geo/country';
	
	const XML_PATH_GEO_CITY = 'opc/geo/city';

	const KEYSALT = "aghtUJ6y";

	public function ambassadorTestAction()
	{
		echo "Tigger sendAutoAmbassadorEmail<br>http://shop.monogramathome.com/ambassadorTest/index/ambassadorTest";
		$api = Mage::getModel('opc/observer');
		print_r($api->sendAutoAmbassadorEmail());
	}
		
	/**
	 * Get one page checkout model
	 *
	 * @return Mage_Checkout_Model_Type_Onepage
	 */
	public function getOnepage(){
		return Mage::getSingleton('checkout/type_onepage');
	}
	
	protected function _getCart(){
		return Mage::getSingleton('checkout/cart');
	}

	protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
	
	
	/**
	 * Predispatch: should set layout area
	 *
	 * @return Mage_Checkout_OnepageController
	 */
	public function preDispatch()
	{
		parent::preDispatch();
		$this->_preDispatchValidateCustomer();
	
		$checkoutSessionQuote = Mage::getSingleton('checkout/session')->getQuote();
		if ($checkoutSessionQuote->getIsMultiShipping()) {
			$checkoutSessionQuote->setIsMultiShipping(false);
			$checkoutSessionQuote->removeAllAddresses();
		}
	
		if (!$this->_canShowForUnregisteredUsers()) {
			$this->norouteAction();
			$this->setFlag('',self::FLAG_NO_DISPATCH,true);
			return;
		}
	
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
     */
	
    public function indexAction(){
    	# Check is One Page Check Out Enable or Disable.
        if (!Mage::helper('checkout')->canOnepageCheckout()) {
            Mage::getSingleton('checkout/session')->addError($this->__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }

        /* CUSTOM CODE */
        $this->_getCart()->truncate()->save();
        $this->_getSession()->setCartWasUpdated(true);

        # Get Kit ID by request onclick="setLocation('{{store url="ambassador/index/index/starterkit/1467"
        $starterKitId = $this->getRequest()->getParam('starterkit');
        
        if(!isset($starterKitId))
        {
        	$starterKitCollection = Mage::getModel('catalog/product')->getCollection()
        		->addAttributeToFilter('is_starter_kit', 1);

        	$starterKitCollection->setOrder('entity_id', 'ASC');

        	if($starterKitCollection->count())
        		$starterKitId = $starterKitCollection->getFirstItem()->getId();
        }
        
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

        if(Mage::getSingleton('customer/session')->isLoggedIn())
        {
        	$customerObject = Mage::getSingleton('customer/session')->getCustomer();
        	Mage::getSingleton('core/session')->setAmbassadorWebsiteName($customerObject->getUsername());
        }

		//  if(Mage::getSingleton('customer/session')->isLoggedIn())
		//	Mage::getSingleton('customer/session')->logout();
		
		/* CUSTOM CODE ENDS */

        $quote = $this->getOnepage()->getQuote();

        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        
        // init default address
        $this->initDefaultAddress();

        Mage::app()->getCacheInstance()->cleanType('layout');
        
        $this->updateDefaultPayment();
        
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                Mage::getStoreConfig('sales/minimum_order/error_message') :
                Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');

            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure' => true)));
        
        
        #echo "<pre>"; print_r($this->getOnepage()->initCheckout()); echo "</pre>"; die();
        $this->getOnepage()->initCheckout();
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__(Mage::getStoreConfig(self::XML_PATH_TITLE)));
        $this->renderLayout();
    }
	
    /**
     * Check can page show for unregistered users
     *
     * @return boolean
     */
    protected function _canShowForUnregisteredUsers()
    {
    	return Mage::getSingleton('customer/session')->isLoggedIn()
    	|| $this->getRequest()->getActionName() == 'index'
    			|| Mage::helper('checkout')->isAllowedGuestCheckout($this->getOnepage()->getQuote())
    			|| !Mage::helper('checkout')->isCustomerMustBeLogged();
    }

    public function addressAction() 
    {
    	/* $addressModel = Mage::getModel('opc/address');
    	var_dump($addressModel); */

    	$addressCollection = Mage::getModel('opc/address')->getCollection();
    	var_dump($addressCollection);
    }

    public function mailAction() 
    {
	    //$registrationTemplateId = Mage::getStoreConfig('ambassador_email_settings/registration_email/template');
	    
	    /*$registrationTemplateId = 11;
	    if(isset($registrationTemplateId) && $registrationTemplateId != "")
	    {
    		$customerObject = Mage::getModel('customer/customer')->load(131);

	    	//Variables for Confirmation Mail.
			$emailTemplateVariables = array();
			$emailTemplateVariables['ambassador_name'] = $customerObject->getName();

			$receiverDetail['name'] = "Hitarth Pattani";
			$receiverDetail['email'] = "hitarth.zwt@gmail.com";

	    	var_dump(Mage::helper('opc')->sendNewsletterMail($registrationTemplateId, $emailTemplateVariables, $receiverDetail));
	    }*/

	    $newsletterEmail = Mage::getModel('opc/newsletter_email')->getCollection();
	    var_dump($newsletterEmail->getData());
    }

    /*
    public function checkAction() {
    	$attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'username');
    	echo "<pre />";
    	$validateRules = $attributeModel->getValidateRules();
    	$validateRules = unserialize($validateRules);
    	$validateRules['input_validation'] = 'alphanum';
    	$attributeModel->setValidateRules(serialize($validateRules));
    	$attributeModel->save();
    	print_r($validateRules);
    }
    */

    /* public function configAction() {
    	echo "<pre>";
    	print_r(Mage::getConfig()->getNode()->xpath('//global/models//rewrite'));
    } */

    public function headerAction()
    {
    	$queryString = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(self::KEYSALT), urldecode(base64_decode($_SERVER['QUERY_STRING'])), MCRYPT_MODE_CBC, md5(md5(self::KEYSALT))), "\0");
		parse_str($queryString);
		
		if(!empty($username) && !empty($password)) 
		{
			$customerCollection = Mage::getModel('customer/customer')->getCollection()
				->addAttributeToFilter('username', $username);

			if($customerCollection->count())
			{
				$email = $customerCollection->getFirstItem()->getEmail();

				$session = Mage::getSingleton('customer/session');

				try 
			    {
	                $session->login($email, $password);
	            } 
	            catch (Exception $e) { }
			}			
		}

		Mage::getSingleton('core/session')->setMlmHeader(1);
		
    	$this->getResponse()->clearHeaders();
    	$this->loadLayout();
    	$this->renderLayout();
    }
}