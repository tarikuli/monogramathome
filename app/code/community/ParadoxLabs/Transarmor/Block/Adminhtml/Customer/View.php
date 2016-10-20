<?php
/**
 * First Data TransArmor integration - Customer card manager - Wrapper and card list
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

class ParadoxLabs_Transarmor_Block_Adminhtml_Customer_View extends Mage_Adminhtml_Block_Template
{
	public function _construct() {
		parent::_construct();
		
		$this->setTemplate( 'transarmor/manage.phtml' );
		$this->setCustomer( Mage::getModel('customer/customer')->load( $this->getRequest()->getParam('id') ) );
		
		$payment = Mage::getModel('transarmor/payment');
		$payment->setCustomer( $this->getCustomer() )
				->setStore( $this->getCustomer()->getStore()->getId() );
		$this->setPayment( $payment );
		
		$card = $this->getRequest()->getParam('c');
		
		$this->setCard( $this->getPayment()->getPaymentInfoById( $card ) );
	}

	public function getCards() {
		$cards	= $this->getPayment()->getPaymentInfo( $this->getCustomer()->getId() );
		
		/**
		 * Get customer's active orders and check for card conflicts.
		 */
		$orders	= Mage::getModel('sales/order')->getCollection()
						->addAttributeToSelect( '*' )
						->addAttributeToFilter( 'customer_id', $this->getCustomer()->getId() )
						->addAttributeToFilter( 'state', array('nin' => array( Mage_Sales_Model_Order::STATE_COMPLETE, Mage_Sales_Model_Order::STATE_CLOSED, Mage_Sales_Model_Order::STATE_CANCELED ) ) );
		
		if( $cards !== false && count($cards) > 0 ) {
			foreach( $cards as $card ) {
				$card->setInUse(0);
				
				if( count($orders) > 0 ) {
					foreach( $orders as $order ) {
						if( $order->getExtCustomerId() == $card->getId() && $order->getPayment()->getMethod() == 'transarmor' ) {
							// If we found an order with this card that is not complete, closed, or canceled,
							// it is still active and the payment ID is important. No editey.
							$card->setInUse(1);
							break;
						}
					}
				}
			}
		}
		
		return $cards;
	}

	public function getCcAvailableTypes()
	{
		$_types = Mage::getConfig()->getNode('global/payment/cc/types')->asArray();

		$types = array();
		foreach ($_types as $data) {
			if (isset($data['code']) && isset($data['name'])) {
				$types[$data['code']] = $data['name'];
			}
		}
		
		$avail = explode( ',', Mage::getModel('transarmor/payment')->getConfigData('cctypes') );
		foreach( $types as $c => $n ) {
			if( !in_array($c, $avail) ) {
				unset($types[$c]);
			}
		}
		
		return $types;
	}
	
	public function getCcMonths()
	{
		$months = Mage::app()->getLocale()->getTranslationList('month');
		foreach ($months as $key => $value) {
			$monthNum = ($key < 10) ? '0'.$key : $key;
			$months[$key] = $monthNum . ' - ' . $value;
		}
		
		return $months;
	}

	public function getCcYears()
	{
		$first = date("Y");
		for ($index=0; $index <= 10; $index++) {
			$year = $first + $index;
			$years[$year] = $year;
		}
		
		return $years;
	}
	
	public function hasVerification()
	{
		return $this->getPayment()->getConfigData('useccv');
	}
	
	public function isEdit()
	{
		return ($this->getCard()->getId() > 0 ? 1 : 0);
	}
	
	public function isAjax()
	{
		return $this->getRequest()->getParam('isAjax');
	}
}
