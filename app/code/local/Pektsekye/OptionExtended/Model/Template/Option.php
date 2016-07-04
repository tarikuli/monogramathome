<?php

class Pektsekye_OptionExtended_Model_Template_Option extends Mage_Core_Model_Abstract
{	
    public function _construct()
    {
        parent::_construct();
        $this->_init('optionextended/template_option');
    }

    
    public function loadStoreFields($storeId)
    {
      $row = $this->getResource()->getStoreFields((int) $this->getId(), $storeId);
      $this->setTitle($row['title']);
      $this->setStoreTitle($row['store_title']);
      $this->setPrice(number_format($row['price'], 2, null, ''));
      $this->setPriceType($row['price_type']);      
      $this->setStorePrice($row['store_price']);
      $this->setNote($row['note']);
      $this->setStoreNote($row['store_note']);  

      return $this;            
    }

    public function getLastRowId()
    {
      return $this->getResource()->getLastRowId((int) $this->getTemplateId());
    }

    public function getNextId()
    {
      return $this->getResource()->getNextId();
    }
    
    public function getValueTitles()
    {
      return $this->getResource()->getValueTitles((int) $this->getId());
    }
    
    public function deleteValues()
    {
        $this->getResource()->deleteValuesWithChidrenUpdate((int) $this->getTemplateId(), (int) $this->getId());
    }   
    
    public function deletePrice()
    {
        $this->getResource()->deletePrice((int) $this->getId());
    }    
       
}
