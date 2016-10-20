<?php
/**
 * First Data TransArmor integration - Customer card manager - tab addition
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

class ParadoxLabs_Transarmor_Block_Adminhtml_Customer_Tab extends Mage_Adminhtml_Block_Template
	implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	public function getTabLabel()
	{
		return $this->__('First Data TransArmor');
	}

	public function getTabTitle()
	{
		return $this->__('First Data TransArmor');
	}

	public function canShowTab()
	{
		return Mage::getModel('transarmor/payment')->isAvailable();
	}

	public function isHidden()
	{
		return false;
	}

	public function getAfter()
	{
		return 'tags';
	}
}
