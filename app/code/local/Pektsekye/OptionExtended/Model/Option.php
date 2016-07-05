<?php

class Pektsekye_OptionExtended_Model_Option extends Mage_Core_Model_Abstract
{	
    public function _construct()
    {
        parent::_construct();
        $this->_init('optionextended/option');
    }
}