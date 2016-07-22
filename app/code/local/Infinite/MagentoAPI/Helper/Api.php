<?php

class Infinite_MagentoAPI_Helper_Api extends Infinite_MagentoAPI_Helper_Log
{
	protected $_apiUrl;

	public function login($params, $customer)
	{
		$data = array(
			'username' => $customer->getUsername(),
			'password' => (isset($params['login'])? $params['login']['password']: $params['password']),
		);

		$response = $this->call('login', $data);
	}

	public function logout($customer)
	{
		$data = array(
			'username' => $customer->getUsername(),
		);
		$response = $this->call('logout', $data);
	}

	public function registration($params, $customer)
	{
		$data = array(
			'username' => $customer->getUsername(), 
			'password' => $params['password'],
			'sponsor_name' => 'admin', 
			'fullname' => $customer->getName(), 
			'address1' => $params['street'][0], 
			'address2' => 'N/A', 
			'postcode' => $params['postcode'], 
			'email' => $params['email'], 
			'package' => 'TEST PACKAGE', 
			'mobile' => $params['telephone'], 
		);

		if(isset($params['street'][1]) && trim($params['street'][1]) != "")
			$data['address2'] = $params['street'][1];

		$response = $this->call('registration', $data);
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

	public function purchase($orderIds)
	{
		foreach($orderIds as $orderId)
		{
			$orderObject = Mage::getModel('sales/order')->load($orderId);

			if($orderObject->getCustomerId())
			{
				$customerObject = Mage::getModel('customer/customer')->load($orderObject->getCustomerId());

				$data = array(
					'user_name' => $customerObject->getUsername(), 
					'order_id' => $orderObject->getIncrementId(), 
					'purchase_datetime' => $orderObject->getCreatedAt(),
				);

				$totalAmount = 0; $orderItems = $orderObject->getAllItems();
				foreach($orderItems as $item)
				{
					$data['product_details'][] = array(
						'product_name' => $item->getName(), 
						'quantity' => intval($item->getQtyOrdered()), 
						'price' => floatval($item->getPrice()), 
						'sub_total' => (intval($item->getQtyOrdered()) * floatval($item->getPrice()))
					);
					$totalAmount += (intval($item->getQtyOrdered()) * floatval($item->getPrice()));
				}
				$data['total_amount'] = $totalAmount;

				$response = $this->call('purchase', $data);
			}
		}
	}

	public function call($method, $data)
	{
		$apiUrl = $this->_getApiUrl();
		$apiUrl .= DS . $method;

		try {
			$handle = curl_init($apiUrl);
			curl_setopt($handle, CURLOPT_POST, true);
			curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($handle);
			curl_close($handle);

			$this->info('REQUEST (' . $method . ') : ' . json_encode($data));

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
			$this->_apiUrl = "http://infinitemlm.com/mlm-demo/Monogram/backoffice/magento_api";

		return $this->_apiUrl;
	}
}