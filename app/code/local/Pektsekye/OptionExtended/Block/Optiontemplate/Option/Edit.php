<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Option_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'option_id';
        $this->_blockGroup = 'optionextended';        
        $this->_controller = 'optiontemplate_option';
 
        
        $this->_addButton('back_templates_button', array(
            'label'   => Mage::helper('core')->__('Templates'),
            'onclick' => 'setLocation(\'' . $this->getBackTemplatesUrl() .'\')',
            'class'   => 'back'
        ));    
      
        $this->_addButton('back_template_button', array(
            'label'   => Mage::helper('adminhtml')->__('Edit Template'),
            'onclick'   => 'setLocation(\'' . $this->getBackTemplateUrl() .'\')',
            'class'   => 'back'
        ));
        
        $this->_addButton('back_options_button', array(
            'label'   => Mage::helper('adminhtml')->__('Options'),
            'onclick' => 'setLocation(\'' . $this->getBackUrl() .'\')',
            'class'   => 'back'
        ));  
        
        parent::__construct();
        
        $this->_removeButton('back');         
        $this->_removeButton('reset'); 
        
        if (!is_null(Mage::registry('current_option')->getId())) {                
          $this->_addButton('dulicate_button', array(
              'label'   => Mage::helper('catalog')->__('Duplicate'),
              'onclick' => 'setLocation(\'' . $this->getDuplicateUrl() .'\')',
              'class'   => 'add'
          ));
        }                      
        
        $this->_addButton('save_and_edit_button', array(
            'label'   => Mage::helper('adminhtml')->__('Save and Continue Edit'),
            'onclick' => "saveAndContinueEdit()",
            'class'   => 'save'
        ),1);  
        

                
        $this->_formScripts[] = '
                optionExtended.aboveOption      = "'. $this->__('Above Option') .'";
                optionExtended.beforeOption     = "'. $this->__('Before Option') .'";
                optionExtended.belowOption      = "'. $this->__('Below Option') .'";
                optionExtended.grid             = "'. $this->__('Grid') .'";
                optionExtended.gridcompact      = "'. $this->__('Grid Compact') .'";                
                optionExtended.list             = "'. $this->__('List') .'";
                optionExtended.mainImage        = "'. $this->__('Main Image') .'";
                optionExtended.colorPicker      = "'. $this->__('Color Picker') .'";
                optionExtended.colorPickerSwap  = "'. $this->__('Picker & Main') .'";
                optionExtended.onTypeChange();
                function saveAndContinueEdit(){ editForm.submit($("edit_form").action + "back/edit/") };
             ';        
    
    }


    public function getHeaderText()
    {
        if (!is_null(Mage::registry('current_option')->getId())) {
            return Mage::registry('current_option')->getTitle();
        } else {
            return $this->__('Add Option');
        }
    }

    public function getDuplicateUrl()
    {
        return $this->getUrl('*/*/duplicate', array('_current' => true));
    }
    
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index', array('template_id' => $this->getRequest()->getParam('template_id')));
    } 
    
    public function getBackTemplateUrl()
    {
       return $this->getUrl('*/optiontemplate/edit', array('template_id' => $this->getRequest()->getParam('template_id')));              
    } 

    public function getBackTemplatesUrl()
    {
        return $this->getUrl('*/optiontemplate/index');
    } 
        
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('_current' => true));
    }      


}
