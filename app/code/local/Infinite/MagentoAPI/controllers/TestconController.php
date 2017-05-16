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
      */
    public function testMethodAction()
    {
    	echo "<br>Hello world Jewel";
    	
    	if (!$this->_loadValidOrder()) {
    		echo "<br>1111 Hello world Jewel";
    	}else {
    		echo "<br>2222222222 Hello world Jewel";
    	}
    	
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
     * Call Helper calss _setAmbassadorMarketingEmail
     * http://www.monogramathome.com/jeweltestmodule/testcon/setAmbassadorMarketingEmailMethod/customer_id/183/
     */
    public function setAmbassadorMarketingEmailMethodAction()
    {
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
