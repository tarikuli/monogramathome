<?php

class Infinite_ImageGallery_Model_Mysql4_Gallery extends Mage_Core_Model_Mysql4_Abstract 
{
  	public function _construct() 
  	{
    	$this->_init('imagegallery/gallery', 'entity_id');
  	}

  	protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        // modify create / update dates
        if ($object->isObjectNew() && !$object->hasCreationTime()) {
            $object->setCreationTime(Mage::getSingleton('core/date')->gmtDate());
        }

        $object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());

        return parent::_beforeSave($object);
    }
}