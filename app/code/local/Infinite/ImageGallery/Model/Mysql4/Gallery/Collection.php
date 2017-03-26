<?php

class Infinite_ImageGallery_Model_Mysql4_Gallery_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
      	parent::_construct();
      	$this->_init('imagegallery/gallery');
    }
}