<?php
/**
 * First Data TransArmor integration - Event observers
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


class ParadoxLabs_Transarmor_Model_Observer extends Mage_Catalog_Model_Observer
{
	/**
	 * If we're doing a partial refund, don't mark it as fully refunded
	 * unless the full amount is done.
	 */
	public function processRefund( $observer ) {
		$memo = $observer->getEvent()->getCreditmemo();
		
		if( $memo->getOrder()->getPayment()->getMethod() == 'transarmor' ) {
			if( $memo->getInvoice() && $memo->getInvoice()->getBaseTotalRefunded() < $memo->getInvoice()->getBaseGrandTotal() ) {
				$memo->getInvoice()->setIsUsedForRefund(false);
			}
		}
	}
}
