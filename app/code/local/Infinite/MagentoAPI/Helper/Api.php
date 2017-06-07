<?php

class Infinite_MagentoAPI_Helper_Api extends Infinite_MagentoAPI_Helper_Log
{
    const GROUP_AMBASSADOR = "Ambassador";
    const GROUP_MEMBER = "Member";
    const ATTRIBUTE_SET = "Kit";
    const API_URL = "http://www.dashboard.monogramathome.com/backoffice/magento_api";

    protected $_apiUrl;
    private $needExecuted = true;

	public function login($params, $customer)
	{
		$username = $customer->getUsername();
		if(isset($username))
		{
			$data = array(
				'username' => $customer->getUsername(),
				'password' => (isset($params['login'])? $params['login']['password']: $params['password']),
			);

			if(!empty($data['username']) && !empty($data['password'])){
				$response = $this->call('login', $data);				
				
				$jewelParams['username'] = base64_encode($customer->getUsername());
				$jewelParams['password'] = base64_encode((isset($params['login'])? $params['login']['password']: $params['password']));
				$jewelParams['group_id'] = $customer->getGroupId();
				Mage::getSingleton('core/session')->setJewelParams(json_encode($jewelParams));
				
				Mage::log('setJewelParams username = '. $customer->getUsername().' password = '.(isset($params['login'])? $params['login']['password']: $params['password']));
				$this->info('setJewelParams username = '. $customer->getUsername().' password = '.(isset($params['login'])? $params['login']['password']: $params['password']));
				
			}else{
				$this->info('Problem (' . $method . ') : ' . json_encode($data));
			}

		}
	}

	public function logout($customer)
	{
		$username = $customer->getUsername();
		if(isset($username))
		{
			$data = array(
				'username' => $customer->getUsername(),
			);
			$response = $this->call('logout', $data);
		}
	}

	public function registration($params, $customer)
	{
// 		// Get group Id
// 		$groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
// 		//Get customer Group name
// 		$group = Mage::getModel('customer/group')->load($groupId);
		
// 		if($group->getCode() == self::GROUP_AMBASSADOR ){
			$data = array(
				'username' => $customer->getUsername(), 
				'password' => $params['password'],
				'sponsor_name' => 'shop', 
				'fullname' => $customer->getName(), 
				'address1' => $params['street'][0], 
				'address2' => 'N/A', 
				'postcode' => $params['postcode'], 
				'email' => $params['email'], 
				'package' => 'null', 
				'mobile' => $params['telephone'], 
				'package_id' => 0,
			);
	
			if(isset($params['package']))
				$data['package'] = $params['package'];
	
			if(isset($params['sponsor_name']))
				$data['sponsor_name'] = $params['sponsor_name'];
	
// 			$ambassadorObject = Mage::getSingleton('core/session')->getAmbassadorObject();
// 			if(isset($ambassadorObject))
// 			{
// 				$websitecode = Mage::getSingleton('core/session')->getAmbassadorCode();
// 				$data['sponsor_name'] = $websitecode;
// 			}

			if(!isset($data['sponsor_name']))
			{
				$data['sponsor_name'] = "shop";
			}
	
			if(isset($params['street'][1]) && trim($params['street'][1]) != "")
				$data['address2'] = $params['street'][1];
	
	        //Email notification to customer support
	        #Mage::helper('julfiker_contact/contact')->sendCustomerNotification($customer->getId());
			$response = $this->call('registration', $data);
// 		}
	}

	public function editProfile($params)
	{
		if(Mage::getSingleton('customer/session')->isLoggedIn())
		{
			$customer = Mage::getSingleton('customer/session')->getCustomer();

			$billingAddress = $customer->getPrimaryBillingAddress();

			$data = array(
				'username' => $customer->getUsername(), 
				'fullname' => $customer->getName(), 
				'address1' => $billingAddress->getStreet1(), 
				'address2' => 'N/A', 
				'postcode' => $billingAddress->getPostcode(), 
				'email' => $customer->getEmail(),
				'mobile' => $billingAddress->getTelephone(), 
			);
			
			$street2 = $billingAddress->getStreet2();
			if(isset($street2) && trim($street2) != "")
				$data['address2'] = $street2;

			$response = $this->call('edit_profile', $data);

			if(isset($params['change_password']) && $params['change_password'] == 1) {
				$this->changePassword($params, $customer);
			}
		}
	}

	public function changePassword($params, $customer)
	{
		$data = array(
			'user_name' => $customer->getUsername(), 
			'old_password' => $params['current_password'],
			'new_password' => $params['password'],
		);

		$response = $this->call('change_password', $data);
	}

	public function changePackage($params)
	{
		$data = array(
			'username' => $params['username'], 
			'package' => $params['package']
		);

		$response = $this->call('change_package', $data);
	}

	public function purchase($orderIds)
	{
		foreach($orderIds as $orderId)
		{
			$orderObject = Mage::getModel('sales/order')->load($orderId);
#echo "<pre>"; print_r($orderObject); echo "</pre>"; die();		
Mage::log('1	Load orderId = '. $orderId);			
$this->info('1	Load orderId = '. $orderId);
			/* If Order details exist by Order ID */
			if($orderObject->getCustomerId())
			{
Mage::log('2	Customer if exist = '. $orderObject->getCustomerId());				
$this->info('2	Customer if exist = '. $orderObject->getCustomerId());				
				# Get 
				$memberParams = json_decode(Mage::getSingleton('core/session')->getJewelParams());

				# Get group Id
				#$groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
				
				# Load Customer details by Customer ID
				$customerObject = Mage::getModel('customer/customer')->load($orderObject->getCustomerId());

				# Get customer Group name
				$group = Mage::getModel('customer/group')->load($customerObject->getGroupId());
				
				# Get BECOME AN AMBASSADOR 5 step Data
				$checkoutMethod = Mage::getSingleton('core/session')->getAmbassadorCheckoutMethod();
				
				# IF checkoutMethod data load BECOME AN AMBASSADOR 5 step Data. From AMBASSADOR  
				if($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER)
				{
					# Get Ambassador sud domain name
					$websiteName = Mage::getSingleton('core/session')->getAmbassadorWebsiteNameForApi();
					
					#if(isset($websiteName))
					if(($memberParams->group_id == 5) && ($customerObject->getGroupId() == 4))
					{
Mage::log('3	If websiteName exist = '. $websiteName);					
$this->info('3	If websiteName exist = '. $websiteName);		
				
						# If sub domain ( Ambassador web site ) exist
						#$params = array(
						#	'username' => $websiteName
						#);
						
						## Write your code for member become an AMBASSADOR ## 
						if(($memberParams->group_id == 5) && ($customerObject->getGroupId() == 4)){

							Mage::log('4	Logedin Member ID = 5 and Member become Ambassador ID = 4 then Create an Ambassador Account in MLM');
							$this->info('4	Logedin Member ID = 5 and Member become Ambassador ID = 4 then Create an Ambassador Account in MLM');
							
							$billingAddress = $customerObject->getPrimaryBillingAddress();
							
							$params = array(
									'username' => base64_decode($memberParams->username),
									'password' => base64_decode($memberParams->password),
									'street' => array($billingAddress->getStreet1()),
									'postcode' => $billingAddress->getPostcode(),
									'email' => $customerObject->getEmail(),
									'telephone' => $billingAddress->getTelephone(),
									'sponsor_name' => $this->_getStoreNameByWebSiteId( $customerObject->getWebsiteId())
							);
							
							
						}

						
						## Write your code for member become an AMBASSADOR ##

						foreach($orderObject->getAllItems() as $orderItem)
						{
							$productObject = Mage::getModel('catalog/product')->load($orderItem->getProductId());

							if($productObject->getId())
								$params['package'] = $productObject->getSku();
						}	
						
						# Comment by Jewel on 05192017
						#$this->changePackage($params);
						$this->registration($params, $customerObject);
						
// ## Write your code for member become an AMBASSADOR ##
// $ambassadorQueueCollection = Mage::getModel('julfiker_contact/ambassadorqueue')
// 									->getCollection()
// 									->addFieldToFilter('domain_id', strtolower($customerObject->getUsername()));
														
														
// $queue = Mage::getModel('julfiker_contact/ambassadorqueue')->load($ambassadorQueueCollection->getFirstItem()->getId());
						

// $memberParams = json_decode(Mage::getSingleton('core/session')->getJewelParams());

// echo "<pre>---------------------00------------------------------</pre>";
// echo "<pre>"; print_r($memberParams); echo "</pre>";
// echo "<pre>---------------------11------------------------------</pre>";

// $queryString['username'] = base64_decode($memberParams->username);
// $queryString['password'] = base64_decode($memberParams->password);
// echo "<pre>"; print_r($queryString); echo "</pre>";

// echo "<pre>---------------------33------------------------------</pre>";
// echo "<pre>"; print_r($group); echo "</pre>";

        		
// echo "<pre>---------------------0------------------------------</pre>";
// echo "<pre>"; print_r($queue); echo "</pre>";														
// echo "<pre>---------------------1------------------------------</pre>";

// Mage::log("-------------------2---------------------");
// Mage::log($customerObject);
// echo "<pre>"; print_r($customerObject); echo "</pre>"; die();

// ## Write your code for member become an AMBASSADOR ##	
	
					}
					else
					{ 

Mage::log('5	If new AMBASSADOR going to register without login as Member Pass= '. base64_decode($memberParams->password));
$this->info('5	If new AMBASSADOR going to register without login as Member Pass= '. base64_decode($memberParams->password));

						# If no sub domain exist and new AMBASSADOR going to register. 
						$billingAddress = $customerObject->getPrimaryBillingAddress();

						$params = array(
							'password' => Mage::getSingleton('core/session')->getCurrentCheckoutCustomerPassword(),
							'street' => array($billingAddress->getStreet1()),
							'postcode' => $billingAddress->getPostcode(),
							'email' => $customerObject->getEmail(),
							'telephone' => $billingAddress->getTelephone(),
							'sponsor_name' => $this->_getStoreNameByWebSiteId( $customerObject->getWebsiteId())
						);

						foreach($orderObject->getAllItems() as $orderItem)
						{
							$productObject = Mage::getModel('catalog/product')->load($orderItem->getProductId());

							if($productObject->getId())
								$params['package'] = $productObject->getSku();
						}					

						$this->registration($params, $customerObject);
					}
					
					Mage::getSingleton('core/session')->unsAmbassadorCheckoutMethod();

					Mage::getSingleton('core/session')->unsAmbassadorWebsiteNameForApi();
				}
				else
				{  
Mage::log('7 I am purchesing from Normal checkout Page');
$this->info('7	I am purchesing from Normal checkout Page');
					
					Mage::getSingleton('core/session')->unsAmbassadorObject();
					Mage::getSingleton('core/session')->unsAmbassadorCheckoutMethod();
					Mage::getSingleton('core/session')->unsAmbassadorWebsiteNameForApi();
					Mage::getSingleton('core/session')->unsAmbassadorWebsiteName();
					Mage::getSingleton('core/session')->unsAmbassadorBillingInfo();
					Mage::getSingleton('core/session')->unsAmbassadorProfileInfo();
					Mage::getSingleton('core/session')->unsAmbassadorDashboardParams();

					# IF checkoutMethod data load from Member or Customer
					
					# Load Customer  checkoutMethod data.
					$checkoutMethod = $this->getOnepage()->getCheckoutMethod();
					
					if(isset($checkoutMethod) && $checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER)
					{

						$billingAddress = $customerObject->getPrimaryBillingAddress();
						
						$params = array(
							'password' => Mage::getSingleton('core/session')->getCurrentCheckoutCustomerPassword(),
							'street' => array($billingAddress->getStreet1()),
							'postcode' => $billingAddress->getPostcode(),
							'email' => $customerObject->getEmail(),
							'telephone' => $billingAddress->getTelephone()
						);
						/* Comment By Jewel If  coming from Defult checkout/cart/ page then Dont Register Member or Customer in MLM*/
						#$this->registration($params, $customerObject);
					}
				}

				/* ########### If Account Type Member Set user_name = Ambassador Account name ########### */
// 				if($group->getCode() != self::GROUP_AMBASSADOR ){
// Mage::log('8	IF GROUP_AMBASSADOR = '. $customerObject->getWebsiteId());					
// $this->info('8	IF GROUP_AMBASSADOR = '. $customerObject->getWebsiteId());
// 					$userName = $this->_getStoreNameByWebSiteId( $customerObject->getWebsiteId());
// 				}else {
// 					$userName = $customerObject->getUsername();
// 				}

				# This purchase is occured from which website.
// 				if($this->_getStoreNameByWebSiteId( $customerObject->getWebsiteId()) == "Admin"){
// 					$userName = $customerObject->getUsername();
// 				}elseif(($memberParams->group_id == 5) && ($customerObject->getGroupId() == 4)){
// 					$userName = $customerObject->getUsername();
				if($customerObject->getGroupId() == 4){
					$userName = $customerObject->getUsername();
				}else{
					$userName = $this->_getStoreNameByWebSiteId( $customerObject->getWebsiteId());
				}
				/* ########### If Account Type Member Set user_name = Ambassador Account name ########### */
				
				$data = array(
					'user_name' => $userName, 
					'order_id' => $orderObject->getIncrementId(), 
					'purchase_datetime' => $orderObject->getCreatedAt(),
				);

				$totalAmount = 0; 
				$orderItems = $orderObject->getAllItems();
				foreach($orderItems as $item)
				{
					$productId = $item->getProductId();

					$productObject = Mage::getModel('catalog/product')->load($productId);
					$itemPrice = $item->getPrice();
					if($productObject->getId())
					{
						$appliedCommission = $productObject->getApplyCommission();
						if(isset($appliedCommission) && $appliedCommission == 0)
							$itemPrice = 0;
					}
#."<br>Purchaser email = ".$customerObject->getEmail()
					$data['product_details'][] = array(
						'product_name' => $item->getName()."<br>Purchaser email = ".$customerObject->getEmail(), 
						'quantity' => intval($item->getQtyOrdered()), 
						'price' => floatval($itemPrice), 
						'sub_total' => (intval($item->getQtyOrdered()) * floatval($itemPrice))
					);
					$totalAmount += (intval($item->getQtyOrdered()) * floatval($itemPrice));

                    /** Adding to queue processing multi store dynamically */
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
                    $attributeSetModel->load($product->getAttributeSetId());
                    $attributeSetName  = $attributeSetModel->getAttributeSetName();
                    
                    /*  Comment by Jewel */
                    #if(0 == strcmp($attributeSetName, self::ATTRIBUTE_SET)) {
                    #    $this->_addQueue($customerObject);
                    #}
                    
                    if(0 == strcmp($attributeSetName, self::ATTRIBUTE_SET)) {
                    	$attributeCheck[] = 1;
                    }else{
                    	$attributeCheck[] = 0;
                    }
				}
				
				/* Check If any non kit Product exist */
				if(array_sum($attributeCheck) == count($attributeCheck)) {
					/* If only KIT in Product then Add to QUE for create sub domain */
					Mage::log('_addQueue function called');
					$this->info('_addQueue function called');
					
					$this->needExecuted = true;
					$this->_addQueue($customerObject);
					$this->_sendAmbassadorWelcomeEmail($customerObject);
					# Add Ambassador Marketing Emails
					$this->_setAmbassadorMarketingEmail($customerObject->getId());
	
				}
				
				$giftVoucherDiscount = floatval(abs($orderObject->getGiftVoucherDiscount()) + abs($orderObject->getDiscountAmount()));
				$data['total_amount'] = floatval(abs($totalAmount)- abs($giftVoucherDiscount));

Mage::log("9	Order# = ". $orderId." SubTotal Amount = ".abs($totalAmount)." GiftVoucherCerDiscount = ".abs($orderObject->getGiftVoucherDiscount())." UseGiftCreditAmount = ".abs($orderObject->getDiscountAmount()));
$this->info("9	Order# = ". $orderId." SubTotal Amount = ".abs($totalAmount)." GiftVoucherCerDiscount = ".abs($orderObject->getGiftVoucherDiscount())." UseGiftCreditAmount = ".abs($orderObject->getDiscountAmount()));
				
				$response = $this->call('purchase', $data);
			}
		}
	}

	public function getOnepage()
	{
		return Mage::getSingleton('checkout/type_onepage');
	}

	public function call($method, $data)
	{
		$apiUrl = $this->_getApiUrl();
		$apiUrl .= DS . $method;

		try {
			$this->info('REQUEST (' . $method . ') : ' . json_encode($data));
			
			$handle = curl_init($apiUrl);
			curl_setopt($handle, CURLOPT_POST, true);
			curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($handle);
			curl_close($handle);

			$responseArray = json_decode($response, true);
			if($responseArray['status'])
				$this->info('SUCCESS (' . $method . ') : ' . $response);
			else
				$this->error($method . ' : ' . $response);

			return $responseArray;
		}
		catch(Exception $e) {
			$this->error($e->getMessage());
		}
	}

	protected function _getApiUrl()
	{
		if(!isset($this->_apiUrl))
			$this->_apiUrl = self::API_URL;

		return $this->_apiUrl;
	}

    /**
     * Adding queue for ambassador upon purchased kit type product
     *
     * @param object $customer
     * return void
     */
    protected function _addQueue($customer) {
        if ($this->needExecuted) {
            $collection = Mage::getModel('julfiker_contact/ambassadorqueue')->getCollection()->addFieldToFilter('domain_id', strtolower($customer->getUsername()));
            $queue = Mage::getModel('julfiker_contact/ambassadorqueue')->load($collection->getFirstItem()->getId());
            if (!$queue->getId()) {
                $queue->setDomainId(strtolower($customer->getUsername()))
                    ->setCustomerId($customer->getId())
                    ->save();

                $this->_customerAssignGroupToAmbassador($customer);
                $this->needExecuted = false;
            }
        }
    }

    /**
     * Ambassador as group assign to customer
     * if group code not found in db, it will create dynamically and assign to customer.
     *
     * @param $customer
     * @return bool
     */
    protected function _customerAssignGroupToAmbassador($customer) {
        if (!$customer)
            return false;

        $code = self::GROUP_AMBASSADOR;
        $collection = Mage::getModel('customer/group')->getCollection() //get a list of groups
            ->addFieldToFilter('customer_group_code', $code);// filter by group code
        $group = Mage::getModel('customer/group')->load($collection->getFirstItem()->getId());

        if (!$group) {
            $group->setCode($code); //set the code
            $group->setTaxClassId(3); //set tax class
            $group->save(); //save group
        }

        $customer->setData( 'group_id', $group->getId());
        $customer->save();

        //Email notification for Ambassador update
        Mage::helper('julfiker_contact/contact')->sendCustomerNotification($customer->getId(), true);
        return $customer;
    }
    
    /**
     * Get Store ID by Web_Site_ID
     * 
     * @param string
     * @return string
     */
    protected function _getStoreNameByWebSiteId($websiteId){
    	$website = Mage::getModel('core/website')->load($websiteId);
    	#$website = explode(".", $website->getCode());
    	$this->info('8	REQUEST WebSite Name: '. $website->getCode());
    	if($website->getCode()== "base"){
    		return "Admin";
    	}
    	return $website->getCode();
    }
    
    public function _setAmbassadorMarketingEmail($customerId ){
    	
    	#echo "<br>".$customerId;
    
    	$newsletterEmailCollection = Mage::getModel('opc/newsletter_email')->getCollection()
							    	->addFieldToFilter('customer_id', $customerId);
	
		Mage::log("From _setAmbassadorMarketingEmail newsletterEmailCollection count = ". $newsletterEmailCollection->count());
							    	
		if($newsletterEmailCollection->count() == 0 ){
			
			$emailTemplateConfiguration = Mage::getStoreConfig('ambassador_email_settings/other_emails/email_items');
			$emailTemplateConfiguration = unserialize($emailTemplateConfiguration);
			 
			foreach($emailTemplateConfiguration as $emailTemplates)
			{
				$newsletterId = $emailTemplates['template'];
				
				Mage::getModel('opc/newsletter_email')
						->setNewsletterId($newsletterId)
						->setCustomerId($customerId)
						->save();
				#echo '<br>opc/newsletter_email newsletterId = '. $newsletterId. " CustomerId = ". $customerId;
				$this->info("Saved in /newsletter_email newsletterId = ". $newsletterId. " CustomerId = ". $customerId);
				Mage::log("Saved in /newsletter_email newsletterId = ". $newsletterId. " CustomerId = ". $customerId);
    		}
		}

    }
    
    protected function _sendAmbassadorWelcomeEmail($customerObject)
    {
    	
    	$websiteName = Mage::getSingleton('core/session')->getAmbassadorWebsiteName();
    	if(isset($websiteName))
    	{
	    	# Get Email#1 - welcome includes ambassador number ( Registration Email )
	    	$registrationTemplateId = Mage::getStoreConfig('ambassador_email_settings/registration_email/template');
	    	if(isset($registrationTemplateId) && $registrationTemplateId != "")
	    	{
		//     	$orderObject = $observer->getOrder();
		    
		//     	$orderObject->setCanSendNewEmailFlag(false);
		    
		//     	$customerObject = Mage::getModel('customer/customer')->load($orderObject->getCustomerId());
		    
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
		    
		    	Mage::log('0. Send Ambassador Welcome Email'.json_encode(array(
		    			'customer_id' => $customerObject->getId(),
		    			'newsletter_id' => $registrationTemplateId,
		    			'receiver' => $receiverDetail,
		    			'status' => $orderEmailStatus
		    	)), null, 'ambassador_emails.log');
	    	}
   		}
    }
}