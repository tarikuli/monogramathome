<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Option_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

  protected function _prepareLayout()
  {
      parent::_prepareLayout();
      if ($this->getIsWysiwygEnabled()) {
          $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);        
          $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
          $this->getLayout()->getBlock('js')->append($this->getLayout()->createBlock('core/template','catalog_wysiwyg_js', array('template'=>'catalog/wysiwyg/js.phtml')));          
      }
  }
  
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
              'id' => 'edit_form',
              'action' => $this->getUrl('*/*/save', array('_current'=>true)),
              'method' => 'post'
              ));

      $form->setUseContainer(true);
      $this->setForm($form);
      return parent::_prepareForm();
  }

      public function getIsWysiwygEnabled()
    {
        $version = Mage::getVersion();
        return version_compare($version, '1.4.0.0') >= 0 && Mage::getSingleton('cms/wysiwyg_config')->isEnabled();
    }
 
}
