<?php
/**
 * First Data TransArmor integration - Helper methods
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


class ParadoxLabs_Transarmor_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getCustomerCards( $id=0 ) {
		if( $id < 1 ) {
			$id = Mage::getSingleton('customer/session')->getCustomer()->getId();
		}
		
		$cards = Mage::getModel('transarmor/card')->getCollection()
						->addFieldToFilter( 'customer_id', $id )
						->addFieldToFilter( 'trans_id', array( 'notnull' => true ) )
						->addFieldToFilter( 'stored', 1 );
		
		return $cards;
	}
}
