<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{

  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $fieldset = $form->addFieldset('optionextended_form', array('legend'=>Mage::helper('adminhtml')->__('General Information')));
     
      $fieldset->addField('title', 'text', array(
          'name'      => 'template_title',    
          'label'     => Mage::helper('adminhtml')->__('Template Name'),
          'required'  => true    
      ));

      $fieldset->addField('code', 'text', array(
          'name'      => 'template_code',
          'label'     => Mage::helper('core')->__('Code'),
          'required'  => true       
      ));
      
      $fieldset->addField('is_active', 'select', array(
          'name'      => 'is_active',
          'label'     => Mage::helper('core')->__('Status'),
          'options'   => array(
              1 => Mage::helper('adminhtml')->__('Enabled'),          
              0 => Mage::helper('adminhtml')->__('Disabled'))        
      ));
      
      $fieldset->addField('product_ids_string', 'hidden', array(
          'name'  => 'product_ids_string'
      ));
      
      $form->setValues(Mage::registry('current_template')->getData());
      $this->setForm($form);
      
      return parent::_prepareForm();
  }

    
}
