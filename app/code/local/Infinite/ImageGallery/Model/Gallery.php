<?php

class Infinite_ImageGallery_Model_Gallery extends Mage_Core_Model_Abstract 
{
	const STATUS_ENABLED = 1;
	const STATUS_DISABLED = 2;

    public function _construct() 
    {
        parent::_construct();
        $this->_init('imagegallery/gallery');
    }

    public function getStatusArray()
    {
    	return array(
    		self::STATUS_ENABLED => Mage::helper('imagegallery')->__('Enabled'),
    		self::STATUS_DISABLED => Mage::helper('imagegallery')->__('Disable')
    	);
    }
}