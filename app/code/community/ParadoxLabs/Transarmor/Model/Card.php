<?php
/**
 * First Data TransArmor integration - Card model
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


class ParadoxLabs_Transarmor_Model_Card extends Mage_Core_Model_Abstract
{
	protected $adtl = null;
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('transarmor/card');
	}
	
	public function getAdditionalInfo( $key='' )
	{
		if( is_null( $this->adtl ) ) {
			$this->adtl = json_decode( parent::getAdditionalInfo(), 1 );
		}
		
		if( !empty($key) ) {
			if( isset( $this->adtl[ strtolower( $key ) ] ) ) {
				return $this->adtl[ strtolower( $key ) ];
			}
			else {
				return '';
			}
		}
		
		return $this->adtl;
	}
	
	public function getFormattedCc()
	{
		if( $this->getLast4() != '' ) {
			return 'XXXX-' . $this->getLast4();
		}
		
		return '';
	}
	
	public function getExpiry()
	{
		return date( 'my', $this->getNotify() + (86400 * 14) );
	}
}
