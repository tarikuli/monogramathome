<?php
class Infinite_CustomerLogin_Model_Observer extends Infinite_MagentoAPI_Helper_Log
{
	const KEYSALT = "aghtUJ6y";
	const AMBASSADOR_GROUP_CODE = 'Ambassador';
	const MEMBER_GROUP_CODE = 'Member';

	public function redirectCustomerToRelativeWebsite($observer)
	{
		if($postData = Mage::app()->getRequest()->getParams('login'))
		{
			if(isset($postData['login']['username']) && isset($postData['login']['password']))
			{
				$email = $postData['login']['username'];
				$password = $postData['login']['password'];

				$customerCollection = Mage::getModel("customer/customer")->getCollection()
						->addAttributeToSelect("*")
						->addAttributeToFilter('email', $email);

				if($customerCollection->count())
				{
					$customerObject = $customerCollection->getFirstItem();

					if($customerObject->getId())
		        	{
	        			$websiteId = $customerObject->getWebsiteId();
	        			if($websiteId != Mage::app()->getWebsite()->getId())
	        			{
							$websiteObject = Mage::getModel('core/website')->load($websiteId);
			        		if($websiteObject->getId())
			        		{
			        			$queryString = "email={$email}&password={$password}";
		    					$queryString = base64_encode(urlencode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(self::KEYSALT), $queryString, MCRYPT_MODE_CBC, md5(md5(self::KEYSALT)))));
		    					$websiteUrl = $websiteObject->getDefaultStore()->getBaseUrl();
								$websiteUrl .= "?{$queryString}";
								Mage::app()->getFrontController()->getResponse()->setRedirect($websiteUrl);
							    Mage::app()->getResponse()->sendResponse();
							    exit;
			        		}
	        			}
		        	}
				}
	        }
		}
	}

	public function checkLogin($observer)
	{
		$queryString = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(self::KEYSALT), urldecode(base64_decode($_SERVER['QUERY_STRING'])), MCRYPT_MODE_CBC, md5(md5(self::KEYSALT))), "\0");
		parse_str($queryString);
		if(!empty($email) && !empty($password)) 
		{
		    $session = $this->_getCustomerSession();

		    $params = Mage::app()->getRequest()->getParams();
		    $params = array_merge($params, array('password' => $password));
		    Mage::app()->getRequest()->setParams($params);
		    
		    try 
		    {
                $session->login($email, $password);
            } 
            catch (Mage_Core_Exception $e) 
            {
                switch ($e->getCode()) 
                {
                    case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                        $value = $this->_getHelper('customer')->getEmailConfirmationUrl($email);
                        $message = $this->_getHelper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                        break;
                    case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                        $message = $e->getMessage();
                        break;
                    default:
                        $message = $e->getMessage();
                }
                $session->addError($message);
                $session->setUsername($email);
            } 
            catch (Exception $e) 
            {
                Mage::logException($e);
                $session->setUsername($e->getMessage());
            }

		    $redirectUrl = Mage::getUrl('customer/account');
		    Mage::app()->getFrontController()->getResponse()->setRedirect($redirectUrl);
		    Mage::app()->getResponse()->sendResponse();
		    exit;
		}

		$websitecode = Mage::app()->getWebsite()->getCode();
		$currentAmbassadorCode = Mage::getSingleton('core/session')->getAmbassadorCode();

		if(!isset($currentAmbassadorCode) || $currentAmbassadorCode != $websitecode)
		{
			Mage::getSingleton('core/session')->setAmbassadorCode($websitecode);

			$customerCollection = Mage::getModel("customer/customer")->getCollection()
				->addAttributeToSelect("*")
				->addAttributeToFilter('username', $websitecode);
			if($customerCollection->count())
				Mage::getSingleton('core/session')->setAmbassadorObject($customerCollection->getFirstItem());
			else
				Mage::getSingleton('core/session')->unsAmbassadorObject();
		}
	}

	protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function customerRegisterSuccess($observer)
	{
		#$ambassadorObject = Mage::getSingleton('core/session')->getAmbassadorObject();
		#if(isset($ambassadorObject))
		# Get BECOME AN AMBASSADOR 5 step Data
		$checkoutMethod = Mage::getSingleton('core/session')->getAmbassadorCheckoutMethod();
		
		Mage::log('11	checkoutMethod = '. $websiteName);
		$this->info('11	checkoutMethod = '. $websiteName);
		
		# IF checkoutMethod data load BECOME AN AMBASSADOR 5 step Data. From AMBASSADOR  
		if($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER)
		{
			$customerObject = $observer->getCustomer();

			$customerGroupCollection = Mage::getModel("customer/group")->getCollection()
												->addFieldToFilter('customer_group_code', self::MEMBER_GROUP_CODE);

			if($customerGroupCollection->count())
			{
				Mage::log('22 '.	$customerGroupCollection);
				$this->info('22 '.	$customerGroupCollection);
				
				$customerObject->setGroupId($customerGroupCollection->getFirstItem()->getId());
				$customerObject->save();
			}
		}
	}
}
?>