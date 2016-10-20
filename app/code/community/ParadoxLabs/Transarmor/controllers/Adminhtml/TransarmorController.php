<?php
/**
 * First Data TransArmor integration - Customer card manager controller
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
 * @category    ParadoxLabs
 * @package     ParadoxLabs_Transarmor
 * @author      Ryan Hoerr <ryan@paradoxlabs.com>
 */

class ParadoxLabs_Transarmor_Adminhtml_TransarmorController extends Mage_Adminhtml_Controller_Action
{
	public function editAction() {
		echo Mage::app()->getLayout()->createBlock('transarmor/adminhtml_customer_edit')->toHtml();
	}
	
	public function saveAction() {
		$card		= intval( $this->getRequest()->getParam('c') );
		$key		= $this->getRequest()->getParam('form_key');
		$payment	= $this->getRequest()->getParam('trans_payment');
		$customer	= Mage::getModel('customer/customer')->load( $this->getRequest()->getParam('id') );
		
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
			}
			catch( Mage_Core_Exception $e ) {
				Mage::log( 'Failed admin card creation/edit.', null, 'firstdata.log' );
				Mage::log( $e->getMessage(), null, 'firstdata.log' );
			}
		}
		
		echo Mage::app()->getLayout()->createBlock('transarmor/adminhtml_customer_view')->toHtml();
	}
	
	public function deleteAction() {
		$card		= intval( $this->getRequest()->getParam('c') );
		$key		= $this->getRequest()->getParam('form_key');
		
		if( $card > 0 && $key == Mage::getSingleton('core/session')->getFormKey() ) {
			Mage::getModel('transarmor/payment')->deletePaymentProfile( $card );
		}
		
		echo 1;
	}
}
