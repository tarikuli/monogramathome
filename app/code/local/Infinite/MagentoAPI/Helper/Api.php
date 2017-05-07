<?php

class Infinite_MagentoAPI_Helper_Api extends Infinite_MagentoAPI_Helper_Log
{
    const GROUP_AMBASSADOR = "Ambassador";
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
	
			$ambassadorObject = Mage::getSingleton('core/session')->getAmbassadorObject();
			if(isset($ambassadorObject))
			{
				$websitecode = Mage::getSingleton('core/session')->getAmbassadorCode();
				$data['sponsor_name'] = $websitecode;
			}
	
			if(isset($params['street'][1]) && trim($params['street'][1]) != "")
				$data['address2'] = $params['street'][1];
	
	        //Email notification to customer support
	        Mage::helper('julfiker_contact/contact')->sendCustomerNotification($customer->getId());
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

			if($orderObject->getCustomerId())
			{
				$customerObject = Mage::getModel('customer/customer')->load($orderObject->getCustomerId());

				$checkoutMethod = Mage::getSingleton('core/session')->getAmbassadorCheckoutMethod();
				if($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER)
				{
					$websiteName = Mage::getSingleton('core/session')->getAmbassadorWebsiteNameForApi();
					if(isset($websiteName))
					{
						$params = array(
							'username' => $websiteName
						);

						foreach($orderObject->getAllItems() as $orderItem)
						{
							$productObject = Mage::getModel('catalog/product')->load($orderItem->getProductId());

							if($productObject->getId())
								$params['package'] = $productObject->getSku();
						}	

						$this->changePackage($params);
					}
					else
					{
						$billingAddress = $customerObject->getPrimaryBillingAddress();
						
						$params = array(
							'password' => Mage::getSingleton('core/session')->getCurrentCheckoutCustomerPassword(),
							'street' => array($billingAddress->getStreet1()),
							'postcode' => $billingAddress->getPostcode(),
							'email' => $customerObject->getEmail(),
							'telephone' => $billingAddress->getTelephone(),
							'sponsor_name' => 'admin'
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
						$this->registration($params, $customerObject);
					}
				}

				$data = array(
					'user_name' => $customerObject->getUsername(), 
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

					$data['product_details'][] = array(
						'product_name' => $item->getName(), 
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
                    
                    /* Comment by Jewel */
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
					$this->_addQueue($customerObject);
				}
				
				$data['total_amount'] = $totalAmount;
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
}