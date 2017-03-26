<?php

class Infinite_ImageGallery_Block_Adminhtml_Image extends Mage_Adminhtml_Block_Widget_Grid_Container 
{
  	public function __construct() 
  	{
    	$this->_controller = 'adminhtml_image';
    	$this->_blockGroup = 'imagegallery';

    	$galleryId = Mage::getSingleton('adminhtml/session')->getGalleryId();
    	$galleryName = '';
        if(isset($galleryId))
        {
            $model = Mage::getModel('imagegallery/gallery')->load($galleryId);
            $galleryName = $model->getName();
        }

    	$this->_headerText = Mage::helper('imagegallery')->__('Manage Images for "%s"', $galleryName);
    	$this->_addButtonLabel = Mage::helper('imagegallery')->__('Add Image');

        $this->_addButton('back_button', array(
            'label'     => Mage::helper('adminhtml')->__('Back'),
            'onclick'   => "setLocation('" . $this->getUrl('*/gallery/*') . "')",
            'class'     => 'back',
        ), 0);
        
    	parent::__construct();
  	} 
}