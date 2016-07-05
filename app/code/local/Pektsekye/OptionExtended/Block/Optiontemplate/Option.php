<?php
class Pektsekye_OptionExtended_Block_Optiontemplate_Option extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'optiontemplate_option';
    $this->_blockGroup = 'optionextended';

    $template = Mage::getModel('optionextended/template')->load((int) $this->getRequest()->getParam('template_id'));

    $this->_headerText = $this->__('%s - Options', $template->getTitle());
    $this->_addButtonLabel = $this->__('Add Option');

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
    
    if (!$template->getHasOptions()){    
      $this->_addButton('import_button', array(
          'label'   => $this->__('Import Options From Product'),
          'onclick' => 'setLocation(\'' . $this->getImportUrl() .'\')',
          'class'   => 'add'
      ));    
    }   
         
    parent::__construct();
            
  }
  
  public function getImportUrl()
  {
      return $this->getUrl('*/optiontemplate_option/import', array('template_id' => $this->getRequest()->getParam('template_id')));
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
