<?php
/**
 * First Data TransArmor integration - Customer card manager - Card add/edit form
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

class ParadoxLabs_Transarmor_Block_Adminhtml_Customer_Edit extends ParadoxLabs_Transarmor_Block_Adminhtml_Customer_View
{
	public function _construct() {
		parent::_construct();
		
		$this->setTemplate('transarmor/edit.phtml');
	}
	
	/**
	 * Get the address block for dynamic state/country selection on forms.
	 */
	public function getAddressBlock() {
		if( is_null( $this->_address ) ) {
			$this->_address = Mage::app()->getLayout()->createBlock('directory/data');
		}
		
		return $this->_address;
	}
}
