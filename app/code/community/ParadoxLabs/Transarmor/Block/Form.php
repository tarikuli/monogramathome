<?php
/**
 * First Data TransArmor integration - checkout form block.
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

class ParadoxLabs_Transarmor_Block_Form extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('transarmor/form.phtml');
    }
    
    public function getPriorCards()
    {
    	return Mage::getModel('transarmor/payment')->getPaymentInfo();
    }
}
