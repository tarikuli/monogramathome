<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Option_Edit_Tab_Values extends  Mage_Adminhtml_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('optionextended/optiontemplate/option/edit/tab/values.phtml');                           
    }
     
    public function getValuesUrl()
    {
        return $this->getUrl('*/optiontemplate_value/index', array('template_id' => (int) Mage::registry('current_option')->getTemplateId(), 'option_id' => (int) Mage::registry('current_option')->getId()));                          
    }

    public function getValueCount()
    {
        return (int) Mage::getResourceModel('optionextended/template_option')->getValueCount((int) Mage::registry('current_option')->getId());                          
    }

}
