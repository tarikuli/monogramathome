<?php
/**
 * Julfiker_Party extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Julfiker
 * @package        Julfiker_Party
 * @copyright      Copyright (c) 2017
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Event front contrller
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Infinite_MagentoAPI_TestconController extends Mage_Core_Controller_Front_Action
{

    /**
      * default action
      * Call self function 
      *
      * @access public
      * @return void
      * @author Julfiker
      * http://www.monogramathome.com/jeweltestmodule/testcon/testMethod
      */
    public function testMethodAction()
    {
    	echo "<br>Hello world Jewel";
    	
    	if (!$this->_loadValidOrder()) {
    		echo "<br>1111 Hello world Jewel";
    	}else {
    		echo "<br>2222222222 Hello world Jewel";
    	}
    	exit();
    }
    
    /**
     * http://shop.monogramathome.com/jeweltestmodule/testcon/test2Method/order_id/136/
     * Call Observer 
     */
    public function test2MethodAction()
    {
    	echo "<br>Hello2 world Jewel";
    	
    	$controller = Mage::getControllerInstance(
    			'Infinite_MagentoAPI_Model_Observer',
    			Mage::app()->getRequest(),
    			Mage::app()->getResponse());
    	
    	$controller->checkoutOnepageSuccess();
    	 
    }
    
    
    /**
     * Call Helper calss method
     */
    public function test3MethodAction()
    {
    	echo "<br>Hello3 world Jewel";
    	$orderId[] = (int) $this->getRequest()->getParam('order_id');
    	$apiHelper = Mage::helper('magento_api/oms');
    	$apiHelper->pushPurchaseToOms($orderId);
    
    }
    
    /**
     * Manual Registration Test
     */
    public function manualRegTestAction($id){
    	

		$email = (string) $this->getRequest()->getParam('customer_id');
		$customer = Mage::getModel("customer/customer");
		$customer->setWebsiteId(Mage::app()->getWebsite('admin')->getId());
		$customer->loadByEmail($email);
		#echo "<pre>"; print_r($customer); echo "</pre>";

		if(empty($customer->getEmail())){
			echo "Ambassador ". $customer->getFirstname()." ".$customer->getLastname(). " email ".$email." not exist.";
			exit();	
		}else{
		
			$memberParams = json_decode(Mage::getSingleton('core/session')->getJewelParams());
			
			if(($customer->getEmail() != $email)){
				echo "Ambassador ". $customer->getFirstname()." ".$customer->getLastname(). " email = ". $email ." not matched";
				exit();
			}elseif (!Mage::getSingleton('customer/session')->isLoggedIn()){
				echo "Please login with user email = ".$email;
				exit();
			}elseif (empty($memberParams->username)){
				echo "Email = ".$email. " ID Password not exist. ";
				exit();
			}
			
		
			$billingAddress = $customer->getPrimaryBillingAddress();
			#echo "<pre>"; print_r($memberParams); echo "</pre>";
			#echo "<pre>"; print_r($customer); echo "</pre>";
			#echo "<pre>"; print_r($billingAddress); echo "</pre>";
				
			echo "<br>". $customer->getEmail()."  ---  ".$customer->getFirstname()." ".$customer->getLastname();
			$apiHelper = Mage::helper('magento_api/api');
			
			echo $apiHelper->_getApiUrl();
			exit();
			$params = array(
				'username' => base64_decode($memberParams->username),
				'password' => base64_decode($memberParams->password),
				'sponsor_name' => "shop",// Update later
				'fullname' => $customer->getFirstname()." ".$customer->getLastname(),
				'address1' => $billingAddress->getStreet()[0],
				'address2' => "N\/A",
				'postcode' => $billingAddress->getPostcode(),
				'email' => $customer->getEmail(),
				'package' => "KIT-000",
				'mobile' => $billingAddress->getTelephone(),
				'package_id' => 0
			);
			
			echo "<pre>Called registration API.</pre>";
			echo "<pre>"; print_r($params); echo "</pre>";
		
			$apiHelper->registration($params, $customer);
			
			if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			
				$orders= Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('customer_id',Mage::getSingleton('customer/session')->getCustomer()->getId());
				
				foreach($orders as $eachOrder){
					$order = Mage::getModel("sales/order")->load($eachOrder->getId());
			
					$items = $order->getAllVisibleItems();
					foreach($items as $item):
					
						if(strpos($item->getSku(), 'KIT') !== false) {
							$order   = Mage::getModel('sales/order')->load($item->getOrderId());
							
							$data = array(
									'user_name' => base64_decode($memberParams->username),
									'order_id' => base64_decode($memberParams->password),
									'purchase_datetime' => $order->getIncrementId(),// Update later
									'product_details' => ["product_name" => "".$item->getName()."<br>Purchaser email = ".$email."","quantity" => 1,"price" => 0,"sub_total" => 0],
									'total_amount' => 0,
							);
							echo "<pre>"; print_r($data); echo "</pre>";
							$apiHelper->call('purchase', $data);
							break;
						}
					
					endforeach;
				}
			}
			
		}
		
		
		exit();
    }
    
    
    /**
     * Call Helper calss _setAmbassadorMarketingEmail
     * http://www.monogramathome.com/jeweltestmodule/testcon/setAmbassadorMarketingEmailMethod/customer_id/183/
     */
    public function setAmbassadorMarketingEmailMethodAction()
    {
    	$this->manualRegTest();
    	exit();
    	echo "<br>setAmbassadorMarketingEmailMethod";
    	$customerId = (int) $this->getRequest()->getParam('customer_id');
    	$apiHelper = Mage::helper('magento_api/api');
    	$apiHelper->_setAmbassadorMarketingEmail($customerId);
    
    }
    
    
    protected function _loadValidOrder($orderId = null)
    {
    	
    	if (null === $orderId) {
    		echo "<br>".$orderId = (int) $this->getRequest()->getParam('order_id');
    	}else {
    		echo "<br>3333333 Hello world Jewel";
    	}
    	
		echo "<pre>"; print_r($orderId); echo "</pre>";
    	$order = Mage::getModel('sales/order')->load($orderId);
    	echo "<pre>"; print_r($order); echo "</pre>"; die();
    
    }
    
    
    

}
