<?php

class Infinite_ImageGallery_Block_Adminhtml_Gallery_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'imagegallery';
        $this->_controller = 'adminhtml_gallery';
        
        $this->_updateButton('save', 'label', Mage::helper('imagegallery')->__('Save Gallery'));
        $this->_updateButton('delete', 'label', Mage::helper('imagegallery')->__('Delete Gallery'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action + 'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if(Mage::registry('gallery_data') && Mage::registry('gallery_data')->getId())
            return Mage::helper('imagegallery')->__("Edit Gallery '%s'", $this->htmlEscape(Mage::registry('gallery_data')->getName()));
        else 
            return Mage::helper('imagegallery')->__('New Gallery');
    }
}