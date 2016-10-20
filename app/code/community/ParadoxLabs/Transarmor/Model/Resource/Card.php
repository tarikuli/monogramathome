<?php
/**
 * First Data TransArmor integration - Card resource model
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

class ParadoxLabs_Transarmor_Model_Resource_Card extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		$this->_init('transarmor/card', 'id');
	}
}
