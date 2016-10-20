<?php
$iwd_av_class = false;

if (! Mage::helper ( 'opc' )->isEnable ()) {
	// check if IWD AddressValidation exists
	$path = Mage::getBaseDir ( 'app' ) . DS . 'code' . DS . 'local' . DS;
	$file = 'IWD/AddressVerification/controllers/OnepageController.php';
	// load IWD OPC class
	if (file_exists ( $path . $file )){		
		// check if IWD AV enabled
		if (Mage::helper ( 'addressverification' )->isAddressVerificationEnabled ()){
			$iwd_av_class = true;
		}
	}
}

if (! $iwd_av_class) {
	require_once Mage::getModuleDir ( 'controllers', 'Mage_Checkout' ) . DS . 'OnepageController.php';
	class IWD_Opc_Checkout_OnepageController extends Mage_Checkout_OnepageController {
		
		/**
		 * Checkout page
		 */
		public function indexAction() {
			$scheme = Mage::app ()->getRequest ()->getScheme ();
			if ($scheme == 'http') {
				$secure = false;
			} else {
				$secure = true;
			}
			
			if (Mage::helper ( 'opc' )->isEnable ()) {
				$this->_redirect ( 'onepage', array (
						'_secure' => $secure 
				) );
				return;
			} else {
				parent::indexAction ();
			}
		}

		public function successAction()
	    {
	        $session = $this->getOnepage()->getCheckout();
	        if (!$session->getLastSuccessQuoteId()) {
	            $this->_redirect('checkout/cart');
	            return;
	        }

	        $lastQuoteId = $session->getLastQuoteId();
	        $lastOrderId = $session->getLastOrderId();
	        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
	        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
	            $this->_redirect('checkout/cart');
	            return;
	        }

	        $session->clear();	        
			
			$websiteName = Mage::getSingleton('core/session')->getAmbassadorWebsiteName();
			if(isset($websiteName))
			{
				$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		        if ($orderId) {
		            $order = Mage::getModel('sales/order')->load($orderId);
		            if ($order->getId()) {
		            	Mage::getSingleton('core/session')->setLastOrderIncrementId($order->getIncrementId());
		            }
		        }
		        
				Mage::getSingleton('customer/session')->logout();
			}
	        
	        $this->loadLayout();

			if(isset($websiteName))
			{
				$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
	        	$this->getLayout()->getBlock('checkout.success')->setTemplate('opc/checkout/success.phtml');
	        }
	        
	        $this->_initLayoutMessages('checkout/session');
	        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
	        $this->renderLayout();
	    }
	}
} else {
	require_once Mage::getModuleDir ( 'controllers', 'IWD_AddressVerification' ) . DS . 'OnepageController.php';
	class IWD_Opc_Checkout_OnepageController extends IWD_AddressVerification_OnepageController {
	}
}
