<?php
/**
 * First Data TransArmor integration - 'Manage My Cards' conditional my account link.
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

class ParadoxLabs_Transarmor_Block_Profilelink extends Mage_Core_Block_Template
{
	public function addProfileLink() {
	    if( ($parentBlock = $this->getParentBlock()) && Mage::getModel('transarmor/payment')->getConfigData('active') ) {
	        $parentBlock->addLink( 'transarmor', 'transarmor/manage/', $this->__("Manage My Cards"), array( '_secure' => true ) );
	    }
	}
}
