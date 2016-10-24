<?php

class Infinite_ImageGallery_Block_Adminhtml_Image_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {

        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'imagegallery';
        $this->_controller = 'adminhtml_image';
        
        $this->_updateButton('save', 'label', Mage::helper('imagegallery')->__('Save Image'));
        $this->_updateButton('delete', 'label', Mage::helper('imagegallery')->__('Delete Image'));
        $this->_removeButton('reset');
        $this->_removeButton('back');

        $this->_addButton('back_button', array(
            'label'     => Mage::helper('adminhtml')->__('Back'),
            'onclick'   => "setLocation('" . $this->getUrl('*/*/', array('gallery_id' => Mage::getSingleton('adminhtml/session')->getGalleryId())) . "')",
            'class'     => 'back',
        ), -1);
    }

    public function getHeaderText()
    {
        if(Mage::registry('image_data') && Mage::registry('image_data')->getId())
            return Mage::helper('imagegallery')->__("Edit Image '%s'", $this->htmlEscape(Mage::registry('image_data')->getTitle()));
        else 
            return Mage::helper('imagegallery')->__('New Image');
    }
}