<?php
/**
 * First Data TransArmor integration - 'Manage My Cards' controller.
 *
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 *
 * Having a problem with the plugin?
 * Not sure what something means?
 * Need custom development?
 * Give us a call!
 *
 * @category	ParadoxLabs
 * @package		ParadoxLabs_Transarmor
 * @author		Ryan Hoerr <ryan@paradoxlabs.com>
 */

class ParadoxLabs_Transarmor_ManageController extends Mage_Core_Controller_Front_Action
{
	public function preDispatch() {
		parent::preDispatch();

		if( !Mage::getSingleton('customer/session')->authenticate($this) ) {
			$this->getResponse()->setRedirect( Mage::helper('customer')->getLoginUrl() );
			$this->setFlag( '', self::FLAG_NO_DISPATCH, true );
		}

		return $this;
	}
	
	public function indexAction() {
		$this->loadLayout();
		$this->_title()->_title('Manage Stored Credit Cards');
    	$this->renderLayout();
	}
	
	public function deleteAction() {
		$card 	= intval( $this->getRequest()->getParam('c') );
		$key 	= $this->getRequest()->getParam('form_key');
		
		if( $card > 0 && $key == Mage::getSingleton('core/session')->getFormKey() ) {
			Mage::getModel('transarmor/payment')->deletePaymentProfile( $card );
		}
		
		$this->_redirect( '*/*' );
	}
	
	public function createAction() {
		$customer	= Mage::getSingleton('customer/session')->getCustomer();
		$card 		= intval( $this->getRequest()->getParam('c') );
		$key 		= $this->getRequest()->getParam('form_key');
		$payment	= $this->getRequest()->getParam('payment');
		
		if( is_numeric( $payment['state'] ) ) {
			$payment['state'] = Mage::getModel('directory/region')->load( $payment['state'] )->getName();
		}
		elseif( !empty( $payment['region'] ) ) {
			$payment['state'] = $payment['region'];
		}
		
		if( count($payment) && $key == Mage::getSingleton('core/session')->getFormKey() ) {
			try {
				if( $card > 0 ) {
					Mage::getModel('transarmor/payment')->createCustomerPaymentProfile( $payment, $customer, $card );
				}
				else {
					Mage::getModel('transarmor/payment')->createCustomerPaymentProfile( $payment, $customer );
				}
				
				Mage::getSingleton('core/session')->addSuccess( $this->__('Saved changes to the card.') );
			}
			catch( Mage_Core_Exception $e ) {
				Mage::getSingleton('core/session')->addError( $e->getMessage() );
			}
		}
		
		$this->_redirect( '*/*' );
	}
}
