<?php

class Pektsekye_OptionExtended_Model_Template_Value extends Mage_Core_Model_Abstract
{	
    public function _construct()
    {
        parent::_construct();
        $this->_init('optionextended/template_value');
    }

    
    public function loadStoreFields($storeId)
    {
      $row = $this->getResource()->getStoreFields((int) $this->getId(), $storeId);
      $this->setTitle($row['title']);
      $this->setStoreTitle($row['store_title']);
      $this->setPrice(number_format($row['price'], 2, null, ''));
      $this->setPriceType($row['price_type']);      
      $this->setStorePrice($row['store_price']);
      $this->setDescription($row['description']);
      $this->setStoreDescription($row['store_description']);              
    }


    public function getNextId()
    {
      return $this->getResource()->getNextId();
    }
       
}
