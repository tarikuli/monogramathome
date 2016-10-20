<?php
class IWD_Opc_Model_Observer
{
	const GROUP_AMBASSADOR = "Ambassador";
	
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
		    }
		}
    }
}