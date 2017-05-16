<?php
class IWD_Opc_Model_Observer
{
	const GROUP_AMBASSADOR = "Ambassador";
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
    			}
    		}
    	}
    }

    public function sendAmbassadorEmail($observer)
    {
    	$websiteName = Mage::getSingleton('core/session')->getAmbassadorWebsiteName();
		if(isset($websiteName))
		{
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

		    	$orderEmailStatus = Mage::helper('opc')->sendNewsletterMail($registrationTemplateId, $emailTemplateVariables, $receiverDetail);

                //Email notification to customer support
                Mage::helper('julfiker_contact/contact')->sendCustomerNotification($customerObject->getId(), true);

		    	Mage::log(json_encode(array(
	    			'customer_id' => $customerObject->getId(), 
	    			'newsletter_id' => $registrationTemplateId, 
	    			'receiver' => $receiverDetail, 
	    			'status' => $orderEmailStatus
    			)), null, "ambassador_emails.log");
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

        	if($currentGroupId == $groupId)
        	{
				$username = $customerObject->getUsername();
    			$password = $observer->getPassword();

        		$queryString = "username={$username}&password={$password}";
				$queryString = base64_encode(urlencode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(self::KEYSALT), $queryString, MCRYPT_MODE_CBC, md5(md5(self::KEYSALT)))));
				$queryString = "?{$queryString}";
				Mage::getSingleton('core/session')->setAmbassadorDashboardParams($queryString);
				return;
        	}
        }

    	Mage::getSingleton('core/session')->unsAmbassadorDashboardParams();
    }

    public function sendAutoAmbassadorEmail()
    {
		$code = self::GROUP_AMBASSADOR;
        $groupCollection = Mage::getModel('customer/group')->getCollection()
            ->addFieldToFilter('customer_group_code', $code);

        echo "<br>1. Called GROUP_AMBASSADOR ".$groupCollection->count();
		if($groupCollection->count())
		{
			$customerCollection = Mage::getModel('customer/customer')->getCollection()
				->addAttributeToSelect("*")
        		->addAttributeToFilter('group_id', $groupCollection->getFirstItem()->getId());

        	$emailTemplateConfiguration = Mage::getStoreConfig('ambassador_email_settings/other_emails/email_items');
		    $emailTemplateConfiguration = unserialize($emailTemplateConfiguration); 
		    foreach($emailTemplateConfiguration as $emailTemplates)
		    	$emailTemplatesOptions[] = $emailTemplates;

        	foreach($customerCollection as $customer)
        		echo "<br>2. Called Customer email: ".$customer->getEmail();
        		$this->_sendAmbassadorEmails($customer, $emailTemplateConfiguration);
		}
    }

    protected function _sendAmbassadorEmails($customer, $emailTemplateConfiguration)
    {
    	# http://www.monogramathome.com/ambassadorTest/index/ambassadorTest
    	Mage::log('Email Started for CUSTOMER: ' . $customer->getId(), null, 'ambassador_emails.log');
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
			echo "<br>3. Called newsletterId: " . $newsletterId;
			$newsletterId = $emailTemplates['template'];

			# Get Records who to sent email and tamplate
			$newsletterEmailCollection = Mage::getModel('opc/newsletter_email')->getCollection()
				->addFieldToFilter('newsletter_id', $newsletterId)
				->addFieldToFilter('customer_id', $customerId);
			
			echo "<br>4. Called newsletterEmailCollection by newsletter_id: " . $newsletterId. " customer_id: ".$customerId;
			echo "<br>5. Count newsletterEmailCollection: " . $newsletterEmailCollection->count();
			#if(!$newsletterEmailCollection->count())
			if($newsletterEmailCollection->count() > 0 )
			{
				$timeHours = self::EMAIL_HOUR_ELAPSE + intval($emailTemplates['hours']);

				echo "<br>6. Before hourdiff > timeHours: " . $hourdiff .">". $timeHours;
				
				if($hourdiff > $timeHours)
				{
					echo "<br>7. After hourdiff > timeHours: " . $hourdiff .">". $timeHours;
					
			    	//Variables for Confirmation Mail.
					$emailTemplateVariables = array();
					$emailTemplateVariables['ambassador_name'] = $customer->getName();

					$receiverDetail['name'] = $customer->getName();
					$receiverDetail['email'] = $customer->getEmail();

			    	$status = Mage::helper('opc/data')->sendNewsletterMail($newsletterId, $emailTemplateVariables, $receiverDetail);

// 			    	Mage::getModel('opc/newsletter_email')
// 			    		->setNewsletterId($newsletterId)
// 			    		->setCustomerId($customerId)
// 			    		->save();

	    			Mage::log(json_encode(array(
		    			'customer_id' => $customerId."",
		    			'newsletter_id' => $newsletterId,
		    			'receiver' => $receiverDetail,
		    			'status' => $status
	    			)), null, "ambassador_emails.log");
				}
			}			
		}
		echo "<br>8. Email Total Send : " . $newsletterEmailCollection->count();
		Mage::log('Email Total Send : ' . $newsletterEmailCollection->count(), null, "ambassador_emails.log");
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
}