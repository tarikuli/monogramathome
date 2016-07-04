<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Option_Import_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
              'id' => 'edit_form',
              'action' => $this->getUrl('*/*/doimport', array('_current'=>true)),
              'method' => 'post'
              ));

      $form->addField('product_id', 'hidden', array('name' => 'product_id'));

      $form->setUseContainer(true);
      $this->setForm($form);  
          
      return parent::_prepareForm();
  }
 
}
