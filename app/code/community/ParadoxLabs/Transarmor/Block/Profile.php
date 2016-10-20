<?php
/**
 * First Data TransArmor integration - Recurring profile view / update payment info.
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

class ParadoxLabs_Transarmor_Block_Profile extends Mage_Core_Block_Template
{
	public function _construct() {
		parent::_construct();
		
		$this->setPayment( Mage::getModel('transarmor/payment') );
		$this->setProfile( Mage::registry('current_recurring_profile') );
		$this->setCustomer( Mage::getModel('customer/customer')->load( $this->getProfile()->getCustomerId() ) );
		
		$this->getProfile()->setStore(Mage::app()->getStore())->setLocale(Mage::app()->getLocale());
		
		$this->getPayment()->setStore( $this->getProfile()->getStoreId() );
		
		$post = Mage::app()->getRequest()->getPost();
		if( !empty( $post ) && $post['form_key'] == Mage::getSingleton('core/session')->getFormKey() ) {
			if( isset($post['set_cc']) && intval($post['payment_id']) > 0 ) {
				$info = $this->getProfile()->getAdditionalInfo();
				$info['payment_id'] = intval($post['payment_id']);
				$this->getProfile()->setAdditionalInfo( $info )->save();
				
				Mage::log( 'Changed payment ID for RP #'.$this->getProfile()->getReferenceId().' to '.$info['payment_id'], null, 'firstdata.log' );
			}
			elseif( isset( $post['set_next_billed'] ) && strtotime( $post['next_billed'] ) > 0 ) {
				$info = $this->getProfile()->getAdditionalInfo();
				$info['next_cycle'] = Mage::getModel('core/date')->gmtTimestamp( $post['next_billed'] );
				$this->getProfile()->setAdditionalInfo( $info )->save();
				
				Mage::log( 'Changed next billing cycle for RP #'.$this->getProfile()->getReferenceId().' to '.date( 'j-F Y h:i', Mage::getModel('core/date')->timestamp( $info['next_cycle'] ) ), null, 'firstdata.log' );
			}
		}
	}
	
	public function isTransarmor() {
		return ($this->getProfile()->getMethodCode() == 'transarmor');
	}
	
	public function getLastBilled() {
		$date = $this->getProfile()->getAdditionalInfo('last_bill');
		return $date > 0 ? date( 'j-F Y h:i', Mage::getModel('core/date')->timestamp( $date ) ) : 'Never';
	}
	
	public function getNextBilled() {
		$date = $this->getNextBilledRaw();
		
		if( $date > 0 ) {
			return date( 'j-F Y h:i', Mage::getModel('core/date')->timestamp( $date ) );
		}
		
		return 'N/A';
	}
	
	public function getNextBilledRaw() {
		$okayStates	= array( 'active', 'pending' );
		$date		= $this->getProfile()->getAdditionalInfo('next_cycle');
		
		if( in_array( $this->getProfile()->getState(), $okayStates ) && $date > 0 ) {
			return $date;
		}
		
		return false;
	}
	
	public function getPaymentInfo() {
		return $this->getPayment()->getPaymentInfoById( $this->getProfile()->getAdditionalInfo('payment_id') );
	}
	
	public function getAllCards() {
		return $this->getPayment()->getPaymentInfo( $this->getCustomer()->getId() );
	}
}
