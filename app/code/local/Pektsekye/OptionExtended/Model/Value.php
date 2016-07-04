<?php

class Pektsekye_OptionExtended_Model_Value extends Mage_Core_Model_Abstract
{	
    public function _construct()
    {
        parent::_construct();
        $this->_init('optionextended/value');
    }
	
	    public function duplicate($oldOptionId, $newOptionId, $newProductId)
    {
		  $this->getResource()->duplicate($oldOptionId, $newOptionId, $newProductId);
		  
        return $this;
	 } 


	 
}