<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Value_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'value_id';
        $this->_blockGroup = 'optionextended';        
        $this->_controller = 'optiontemplate_value';

        $this->_addButton('back_templates_button', array(
            'label'   => Mage::helper('core')->__('Templates'),
            'onclick' => 'setLocation(\'' . $this->getBackTemplatesUrl() .'\')',
            'class'   => 'back'
        ));    
      
        $this->_addButton('back_template_button', array(
            'label'   => Mage::helper('adminhtml')->__('Edit Template'),
            'onclick' => 'setLocation(\'' . $this->getBackTemplateUrl() .'\')',
            'class'   => 'back'
        ));
        
        $this->_addButton('back_options_button', array(
            'label'   => Mage::helper('adminhtml')->__('Options'),
            'onclick' => 'setLocation(\'' . $this->getBackOptionsUrl() .'\')',
            'class'   => 'back'
        ));    
      
        $this->_addButton('back_option_button', array(
            'label'   => $this->__('Edit Option'),
            'onclick' => 'setLocation(\'' . $this->getBackOptionUrl() .'\')',
            'class'   => 'back'
        ));
        
        $this->_addButton('back_values_button', array(
            'label'   => $this->__('Values'),
            'onclick' => 'setLocation(\'' . $this->getBackUrl() .'\')',
            'class'   => 'back'
        ));  
        
        parent::__construct();
        
        $this->_removeButton('back');        
        $this->_removeButton('reset'); 

        $this->_addButton('save_and_edit_button', array(
            'label'   => Mage::helper('adminhtml')->__('Save and Continue Edit'),
            'onclick' => "saveAndContinueEdit()",
            'class'   => 'save'
        ), 1);
        
        $this->_formScripts[] = " function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'back/edit/') } ";        
    
    }


    public function getHeaderText()
    {
        if ( Mage::registry('current_value') && Mage::registry('current_value')->getId() ) {
            return Mage::registry('current_value')->getTitle();
        } else {
            $storeId = (int) $this->getRequest()->getParam('store');    
            $optionTitle = Mage::getModel('optionextended/template_option')
              ->load((int) $this->getRequest()->getParam('option_id'))
              ->loadStoreFields($storeId)
              ->getTitle();         
            return $this->__('Add Value of %s', $optionTitle);
        }
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/index', array('template_id' => $this->getRequest()->getParam('template_id'), 'option_id' => $this->getRequest()->getParam('option_id')));
    } 

    public function getBackOptionUrl()
    {
       return $this->getUrl('*/optiontemplate_option/edit', array('template_id' => $this->getRequest()->getParam('template_id'), 'option_id' => $this->getRequest()->getParam('option_id')));              
    } 
    
    public function getBackOptionsUrl()
    {
       return $this->getUrl('*/optiontemplate_option/index', array('template_id' => $this->getRequest()->getParam('template_id')));
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
