<?php

class Infinite_ImageGallery_Block_Adminhtml_Gallery extends Mage_Adminhtml_Block_Widget_Grid_Container 
{
  	public function __construct() 
  	{
    	$this->_controller = 'adminhtml_gallery';
    	$this->_blockGroup = 'imagegallery';
    	$this->_headerText = Mage::helper('imagegallery')->__('Manage Gallery');
    	$this->_addButtonLabel = Mage::helper('imagegallery')->__('Add Gallery');
    	parent::__construct();
  	} 
}