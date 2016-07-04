<?php

class Pektsekye_OptionExtended_Model_Pickerimage extends Mage_Core_Model_Abstract
{	
    
    public function _construct()
    {
      parent::_construct();
      $this->_init('optionextended/pickerimage');
    }


    public function getImageData()
    {        
      return $this->getResource()->getImageData();                            
    } 


    public function saveImages($images)
    {    
      $this->getResource()->saveImages($images);      
    }

    
}
