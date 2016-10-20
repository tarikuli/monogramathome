<?php
/**
 * First Data TransArmor integration - Card types for configuration
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

class ParadoxLabs_Transarmor_Model_Source_Cardtypes
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'AE', 'label'=>'American Express'),
            array('value' => 'VI', 'label'=>'Visa'),
            array('value' => 'MC', 'label'=>'MasterCard'),
            array('value' => 'DI', 'label'=>'Discover'),
            array('value' => 'JCB', 'label'=>'JCB'),
            // array('value' => 'DC', 'label'=>'Diners Club'), // Magento does not support.
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
    	$tmp = array();
    	
    	foreach( $this->toOptionArray() as $v ) {
    		$tmp[ $v['value'] ] = $v['label'];
    	}
    	
    	return $tmp;
    }
}
