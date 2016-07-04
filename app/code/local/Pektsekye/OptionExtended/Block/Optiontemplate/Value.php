<?php
class Pektsekye_OptionExtended_Block_Optiontemplate_Value extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'optiontemplate_value';
    $this->_blockGroup = 'optionextended';
    
    $template = Mage::getModel('optionextended/template')->load((int) $this->getRequest()->getParam('template_id'));
    $option = Mage::getModel('optionextended/template_option')->load((int) $this->getRequest()->getParam('option_id'));
    $option->loadStoreFields(0);
    
    $this->_headerText = $this->__('%s - %s - Values', $template->getTitle(), $option->getTitle());
    $this->_addButtonLabel = $this->__('Add Value');
    
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
    
    parent::__construct();
            
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
        
  public function getCreateUrl()
  {
      return $this->getUrl('*/*/new', array('_current'=>true));
  }  
    
}
