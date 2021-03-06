<?php
class IWD_Opc_Model_Observer
{
	const GROUP_AMBASSADOR = "Ambassador";
	const GROUP_MEMBER = "Member";
	const KEYSALT = "aghtUJ8y";
	const EMAIL_HOUR_ELAPSE = 0;
	
	public function checkRequiredModules($observer){
		$cache = Mage::app()->getCache();
		
		if (Mage::getSingleton('admin/session')->isLoggedIn()) {
			if (!Mage::getConfig()->getModuleConfig('IWD_All')->is('active', 'true')){
				if ($cache->load("iwd_opc")===false){
					$message = 'Important: Please setup IWD_ALL in order to finish <strong>IWD One Page Checkout</strong> installation.<br />
						Please download <a href="http://iwdextensions.com/media/modules/iwd_all.tgz" target="_blank">IWD_ALL</a> and setup it via Magento Connect.';
				
					Mage::getSingleton('adminhtml/session')->addNotice($message);
					$cache->save('true', 'iwd_opc', array("iwd_opc"), $lifeTime=5);
				}
			}
		}
	}

	public function removeStarterKit($observer) {
		$productIds = array(1432);

		/*if(Mage::app()->getRequest()->getModuleName() != "ambassador")
		{
			$quote = $this->_getCart()->getQuote();
			if ($quote->hasItems() && !$quote->getHasError()) {
				foreach ($quote->getAllItems() as $item) {
				    $productId = $item->getProduct()->getId();
				    if(in_array($productId, $productIds))
				    	$this->_getCart()->removeItem($item->getId())->save();
				}
			}
			$this->_getSession()->setCartWasUpdated(true);
		}*/
	}
	
	protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
	
	public function newsletter($observer){
		$_session = Mage::getSingleton('core/session');

		$newsletterFlag = $_session->getIsSubscribed();
		if ($newsletterFlag==true){
			
			$email = $observer->getEvent()->getOrder()->getCustomerEmail();
			
			$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
	        if($subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED && $subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED) {
	            $subscriber->setImportMode(true)->subscribe($email);
	            
	            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
	            $subscriber->sendConfirmationSuccessEmail();
	        }
			
		}
		
	}
	
	public function applyComment($observer){
		$order = $observer->getData('order');
		
		$comment = Mage::getSingleton('core/session')->getOpcOrderComment();
		if (!Mage::helper('opc')->isShowComment() || empty($comment)){
			return;
		}
		try{
			$order->setCustomerComment($comment);
			$order->setCustomerNoteNotify(true);
			$order->setCustomerNote($comment);
			$order->addStatusHistoryComment($comment)->setIsVisibleOnFront(true)->setIsCustomerNotified(true);
			$order->save();
			$order->sendOrderUpdateEmail(true, $comment);
		}catch(Exception $e){
			Mage::logException($e);
		}
	}

    public function checkoutCartAddProductComplete($observer){
        if (!Mage::getStoreConfig('payment/incontext/enable')){
            return;
        }

        $request = $observer->getRequest();
        $response = $observer->getRequest();
        $returnUrl = $request->getParam('return_url', false);
        if (preg_match('/express\/start/i', $returnUrl)){
            $request->setParam('return_url', Mage::getUrl('checkout/cart', array('_secure'=>true)));
        }
    }

    public function setCustomerDataAfterSuccessAction($observer)
    {
    	$orderIds = $observer->getOrderIds();
    	if(count($orderIds))
    	{
    		$orderObject = Mage::getModel('sales/order')->load($orderIds[0]);
    		if($orderObject->getId())
    		{
    			$customerId = $orderObject->getCustomerId();
    			if(isset($customerId))
    			{
    				$customerObject = Mage::getModel('customer/customer')->load($customerId);
    				
    				$websiteName = Mage::getSingleton('core/session')->getAmbassadorWebsiteName();
					if(isset($websiteName))
					{
						if($websiteName == $customerObject->getUsername())
							Mage::getSingleton('core/session')->setAmbassadorWebsiteNameForApi($websiteName);

						$customerObject->setIsActive(0);
						
						$customerObject->setUsername($websiteName);
						Mage::getSingleton('core/session')->unsAmbassadorWebsiteName();

						$code = self::GROUP_AMBASSADOR;
				        $groupCollection = Mage::getModel('customer/group')->getCollection()
				            ->addFieldToFilter('customer_group_code', $code); 
				        $groupObject = Mage::getModel('customer/group')->load($groupCollection->getFirstItem()->getId());
				        if (!$groupObject) {
				            $groupObject->setCode($code);
				            $groupObject->setTaxClassId(3);
				            $groupObject->save();
				        }
				        $customerObject->setData('group_id', $groupObject->getId());

						$collection = Mage::getModel('julfiker_contact/ambassadorqueue')->getCollection()->addFieldToFilter('domain_id', strtolower($websiteName));
						$queue = Mage::getModel('julfiker_contact/ambassadorqueue')->load($collection->getFirstItem()->getId());
						if (!$queue->getId()) {
							$queue->setDomainId(strtolower($websiteName))
								->setCustomerId($customerObject->getId())
								->save();
						}
					}
					$generalData = Mage::getSingleton('core/session')->getGeneralData();
					if(isset($generalData))
					{
				        $addressObject = Mage::getModel('opc/address');
				        $addressCollection = $addressObject->getCollection()->addFieldToFilter('customer_id', $customerId);
				        if($addressCollection->count())
				        	$addressObject->load($addressCollection->getFirstItem()->getId());
				        $region = $generalData['region'];
				        if(isset($generalData['region_id']) && $generalData['region_id'] != "")
				        {
				        	$regionObject = Mage::getModel('directory/region')->load(12);
				        	$region = $regionObject->getName();
				        }
				        $addressObject->setCustomerId($customerId)
				        	->setAddress(json_encode($generalData['street']))
				        	->setCity($generalData['city'])
				        	->setState($region)
				        	->setCountry($generalData['country_id'])
				        	->setZipcode($generalData['postcode'])
				        	->setTelephone($generalData['telephone'])
				        	->setFax($generalData['fax']);
				        $addressObject->save();
				        
				        Mage::getSingleton('core/session')->unsGeneralData();
					}
					$socialSecurityNumber = Mage::getSingleton('core/session')->getAmbassadorBillingInfo();
					if(isset($socialSecurityNumber) && is_array($socialSecurityNumber) && isset($socialSecurityNumber['ssn_number']))
					{
						$customerObject->setSocialSecurityNumber($socialSecurityNumber['ssn_number']);
						Mage::getSingleton('core/session')->unsAmbassadorBillingInfo();
					}
					$ambassadorProfileInfo = Mage::getSingleton('core/session')->getAmbassadorProfileInfo();
					if(isset($ambassadorProfileInfo))
					{
						if(isset($ambassadorProfileInfo['why_are_you_interested']))
							$customerObject->setWhyAreYouInterested($ambassadorProfileInfo['why_are_you_interested']);
						if(isset($ambassadorProfileInfo['devote_building_business']))
							$customerObject->setDevoteBuildingBusiness($ambassadorProfileInfo['devote_building_business']);
						if(isset($ambassadorProfileInfo['what_needs_dreams']))
							$customerObject->setWhatNeedsDreams($ambassadorProfileInfo['what_needs_dreams']);
						if(isset($ambassadorProfileInfo['how_familiar_social_media']))
							$customerObject->setHowFamiliarSocialMedia($ambassadorProfileInfo['how_familiar_social_media']);
						if(isset($ambassadorProfileInfo['interested_building_business']))
							$customerObject->setInterestedBuildingBusiness($ambassadorProfileInfo['interested_building_business']);

						Mage::getSingleton('core/session')->unsAmbassadorProfileInfo();
					}
					$customerObject->save();
					
					$this->cartProductAction($customerObject->getEmail());
    			}
    		}
    	}
    }

    public function sendAmbassadorEmail($observer)
    {
    	$websiteName = Mage::getSingleton('core/session')->getAmbassadorWebsiteName();
		if(isset($websiteName))
		{
			# Get Email#1 - welcome includes ambassador number ( Registration Email )
			$registrationTemplateId = Mage::getStoreConfig('ambassador_email_settings/registration_email/template');
		    if(isset($registrationTemplateId) && $registrationTemplateId != "")
		    {
				$orderObject = $observer->getOrder();

				$orderObject->setCanSendNewEmailFlag(false);

	    		$customerObject = Mage::getModel('customer/customer')->load($orderObject->getCustomerId());

		    	//Variables for Confirmation Mail.
				$emailTemplateVariables = array();
				$emailTemplateVariables['ambassador_name'] = $customerObject->getName();
				//$emailTemplateVariables['ambassador_number'] = $orderObject->getIncrementId();
				$emailTemplateVariables['ambassador_number'] = $customerObject->getId();
				$emailTemplateVariables['ambassador_website'] = $websiteName;
				$emailTemplateVariables['ambassador_email'] = $customerObject->getEmail();
				$emailTemplateVariables['ambassador_password'] = Mage::getSingleton('core/session')->getCurrentCheckoutCustomerPassword();

				$receiverDetail['name'] = $customerObject->getName();
				$receiverDetail['email'] = $customerObject->getEmail();

		    	$orderEmailStatus = Mage::helper('opc/data')->sendNewsletterMail($registrationTemplateId, $emailTemplateVariables, $receiverDetail);

                //Email notification to customer support
                Mage::helper('julfiker_contact/contact')->sendCustomerNotification($customerObject->getId(), true);

		    	Mage::log('88. Send Ambassador Welcome Email'.json_encode(array(
	    			'customer_id' => $customerObject->getId(), 
	    			'newsletter_id' => $registrationTemplateId, 
	    			'receiver' => $receiverDetail, 
	    			'status' => $orderEmailStatus
    			)), null, 'ambassador_emails.log');
		    }
		}
    }

    public function setAmbassadorParameters($observer)
    {
    	$code = self::GROUP_AMBASSADOR;
        $groupCollection = Mage::getModel('customer/group')->getCollection()
            ->addFieldToFilter('customer_group_code', $code); 

        if($groupCollection->count())
        {
        	$currentGroupId = $groupCollection->getFirstItem()->getId();

    		$customerObject = $observer->getModel();
    		$groupId = $customerObject->getGroupId();

    		$username = $customerObject->getUsername();
    		$password = $observer->getPassword();
    		
        	if($currentGroupId == $groupId)
        	{
        		$queryString = "username={$username}&password={$password}";
				$queryString = base64_encode(urlencode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(self::KEYSALT), $queryString, MCRYPT_MODE_CBC, md5(md5(self::KEYSALT)))));
				$queryString = "?{$queryString}";
				Mage::getSingleton('core/session')->setAmbassadorDashboardParams($queryString);
				
// 				$params['username'] = base64_encode($username);
// 				$params['password'] = base64_encode($password);
// 				$params['account_type'] = self::GROUP_AMBASSADOR;
// 				Mage::getSingleton('core/session')->setJewelParams(json_encode($params));
				
				return;
        	}else{
//         		$params['username'] = base64_encode($username);
//         		$params['password'] = base64_encode($password);
//         		$params['account_type'] = self::GROUP_MEMBER;
//         		Mage::getSingleton('core/session')->setJewelParams(json_encode($params));
        		
//         		$memberParams = json_decode(Mage::getSingleton('core/session')->getMemberParams(json_encode($queryString)));
        		
//         		echo "<pre>---------------------00------------------------------</pre>";
//         		echo "<pre>"; print_r($memberParams); echo "</pre>";
//         		echo "<pre>---------------------11------------------------------</pre>";
        		
//         		$queryString['username'] = base64_decode($memberParams->username);
//         		$queryString['password'] = base64_decode($memberParams->password);
//         		echo "<pre>"; print_r($queryString); echo "</pre>";
        		
//         		echo "<pre>---------------------33------------------------------</pre>";
//         		print_r($currentGroupId."   --- ".$groupCollection->getFirstItem()->getCode());
//         		echo "<pre>---------------------44------------------------------</pre>";
//         		print_r($groupId);
//         		exit();
        	}
        }

    	Mage::getSingleton('core/session')->unsAmbassadorDashboardParams();
    }

    public function sendAutoAmbassadorEmail()
    {
		$code = self::GROUP_AMBASSADOR;
		# Get all Ambassador from customer/group
        $groupCollection = Mage::getModel('customer/group')->getCollection()
            ->addFieldToFilter('customer_group_code', $code);

        Mage::log("sendAutoAmbassadorEmail call from Cron Job", null, "ambassador_emails.log");
        
		if($groupCollection->count())
		{
			$customerIds = [];
			$opcNewsletterEmailCollection = Mage::getModel('opc/newsletter_email')->getCollection();
			foreach($opcNewsletterEmailCollection as $opcCusletter){
				$customerIds[]= $opcCusletter->getCustomerId();
			}
			
			$customerIds = array_unique($customerIds);
        	$emailTemplateConfiguration = Mage::getStoreConfig('ambassador_email_settings/other_emails/email_items');
		    $emailTemplateConfiguration = unserialize($emailTemplateConfiguration); 
		    foreach($emailTemplateConfiguration as $emailTemplates){
		    	$emailTemplatesOptions[] = $emailTemplates;
		    }
		    
		    foreach ($customerIds as $customerId){
		    	$customerCollection = Mage::getModel('customer/customer')->getCollection()
		    		->addAttributeToSelect("*")
		    		->addAttributeToFilter('entity_id', $customerId);
		    	
	    		foreach($customerCollection as $customer){
		    		if(!empty($customer->getEmail())){
		    			Mage::log("2. _sendAmbassadorEmails function called for Customer Id: ".$customer->getId()." Customer email: ".$customer->getEmail(), null, "ambassador_emails.log");
		    			$this->_sendAmbassadorEmails($customer, $emailTemplateConfiguration);
		    		}
	    		}
		    }
		}
    }

    /**
     * Get Store ID by Web_Site_ID
     *
     * @param string
     * @return string
     */
    protected function _getStoreNameByWebSiteId($websiteId){
    	$website = Mage::getModel('core/website')->load($websiteId);
    	if($website->getCode()== "base"){
    		return "shop";
    	}
    	return $website->getCode();
    }
    
    protected function _sendAmbassadorEmails($customer, $emailTemplateConfiguration)
    {
    	set_time_limit(0);
    	# echo "<pre>"; print_r($customer); echo "</pre>"; 
    	$websiteName = $this->_getStoreNameByWebSiteId( $customer->getWebsiteId());
    	
    	# http://www.monogramathome.com/ambassadorTest/index/ambassadorTest
    	$newsletterEmailCollection[] = 0;
    	$customerId = $customer->getId();
        		
		// Time Difference
		$customerSince = $customer->getCreatedAt();

		$currentTimestamp = Mage::getModel('core/date')->timestamp(time());
		$currentDate = date('Y-m-d H:i:s', $currentTimestamp);
		$customerSince = Mage::getModel('core/date')->timestamp(strtotime($customerSince));
		$customerSince = date('Y-m-d H:i:s', $customerSince);
		
		$hourdiff = round((strtotime($currentDate) - strtotime($customerSince)) / 3600, 1);

		foreach($emailTemplateConfiguration as $emailTemplates)
		{
			$newsletterId = $emailTemplates['template'];
			Mage::log("3. newsletterId =  ".$newsletterId, null, "ambassador_emails.log");
			
			# Get Records who to sent email and tamplate
			$newsletterEmailCollection = Mage::getModel('opc/newsletter_email')->getCollection()
												->addFieldToFilter('newsletter_id', $newsletterId)
												->addFieldToFilter('customer_id', $customerId);
			
			Mage::log("4. Count newsletterEmailCollection: " . $newsletterEmailCollection->count(), null, "ambassador_emails.log");
			#if(!$newsletterEmailCollection->count())
			if($newsletterEmailCollection->count() > 0 )
			{
				$timeHours = self::EMAIL_HOUR_ELAPSE + intval($emailTemplates['hours']);

				Mage::log("5. Before hourdiff > timeHours: " . $hourdiff .">". $timeHours, null, "ambassador_emails.log");
				if($hourdiff > $timeHours)
				{
					Mage::log("6. After hourdiff > timeHours: " . $hourdiff .">". $timeHours, null, "ambassador_emails.log");
			    	//Variables for Confirmation Mail.
					$emailTemplateVariables = array();
					$emailTemplateVariables['ambassador_name'] = $customer->getName();
					$emailTemplateVariables['ambassador_number'] = $customer->getId();
					$emailTemplateVariables['ambassador_website'] = $websiteName;
					$emailTemplateVariables['ambassador_email'] = $customer->getEmail();
					$emailTemplateVariables['ambassador_password'] = Mage::getSingleton('core/session')->getCurrentCheckoutCustomerPassword();
					

					$receiverDetail['name'] = $customer->getName();
					$receiverDetail['email'] = $customer->getEmail();

			    	$status = Mage::helper('opc/data')->sendNewsletterMail($newsletterId, $emailTemplateVariables, $receiverDetail);


					try {
						$model = Mage::getModel('opc/newsletter_email');
						$newsletterEmailCollection = Mage::getModel('opc/newsletter_email')->getCollection()
													->addFieldToFilter('newsletter_id', $newsletterId)
													->addFieldToFilter('customer_id', $customerId);
					
						foreach ($newsletterEmailCollection as $key => $value){
							$model->setEntityId($key)->delete();
						}
						Mage::log("customer_id delete : ".$customerId." NewsletterId : ".$newsletterId, null, "ambassador_emails.log");
					
					} catch (Exception $e){
						echo $e->getMessage();
					}
    				    		
    				
    				    		
	    			Mage::log('7. Email send to = '.json_encode(array(
		    			'customer_id' => $customerId."",
		    			'newsletter_id' => $newsletterId,
		    			'receiver' => $receiverDetail,
		    			'status' => $status
	    			)), null, "ambassador_emails.log");
				}
			}			
		}
		

    }

    public function saveGeneralDetails($observer)
    {
    	$generalData = $observer->getRequest()->getPost('general');
    	$customer = $observer->getCustomer();

    	$addressObject = Mage::getModel('opc/address');
        $addressCollection = $addressObject->getCollection()->addFieldToFilter('customer_id', $customer->getId());

        if($addressCollection->count())
        	$addressObject->load($addressCollection->getFirstItem()->getId());

        $addressObject->setCustomerId($customerId)
        	->setAddress(json_encode($generalData['address']))
        	->setCity($generalData['city'])
        	->setState($generalData['state'])
        	->setCountry($generalData['country'])
        	->setZipcode($generalData['zipcode'])
        	->setTelephone($generalData['telephone'])
        	->setFax($generalData['fax']);
        $addressObject->save();
    }

    public function customerSaveBefore($observer)
    {
    	$adminUser = Mage::getSingleton('admin/session')->getUser();
    	if(isset($adminUser) && $adminUser->getId())
    	{
    		if($adminUser->getUsername() == "admin")
    		{
	    		$customerObject = $observer->getEvent()->getDataObject();    	
	    		$socialSecurityNumber = $customerObject->getSocialSecurityNumber();
	    		if(isset($socialSecurityNumber) && trim($socialSecurityNumber) != "")
	    		{
	    			$encryptedSocialSecurityNumber = Mage::getSingleton('core/encryption')->encrypt($socialSecurityNumber); 
	    			$customerObject->setSocialSecurityNumber($encryptedSocialSecurityNumber);
	    		}
	    	}
		}
    }

    public function customerLoadAfter($observer)
    {
    	$customerObject = $observer->getEvent()->getDataObject();    	
    	$socialSecurityNumber = $customerObject->getSocialSecurityNumber();
    	if(isset($socialSecurityNumber) && trim($socialSecurityNumber) != "")
    	{
    		$descryptedSocialSecurityNumber = Mage::getSingleton('core/encryption')->decrypt($socialSecurityNumber); 
    		$customerObject->setSocialSecurityNumber($descryptedSocialSecurityNumber);
    	}

    	$adminUser = Mage::getSingleton('admin/session')->getUser();
    	if(isset($adminUser) && $adminUser->getId())
    	{
    		if($adminUser->getUsername() != "admin")
    		{
    			$socialSecurityNumber = $customerObject->getSocialSecurityNumber();
    			$hiddenSocialSecurityNumber = "XXX-XX-" . substr($socialSecurityNumber, -4);
    			$customerObject->setSocialSecurityNumber($hiddenSocialSecurityNumber);
    		}
    	}
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