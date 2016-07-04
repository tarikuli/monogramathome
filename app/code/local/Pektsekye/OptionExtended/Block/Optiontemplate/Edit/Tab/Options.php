<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Edit_Tab_Options extends  Mage_Adminhtml_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('optionextended/optiontemplate/edit/tab/options.phtml');                           
    }
     
    public function getOptionsUrl()
    {
        return $this->getUrl('*/optiontemplate_option/index', array('template_id' => (int) Mage::registry('current_template')->getId()));                          
    }

    public function getOptionCount()
    {
        return (int) Mage::getResourceModel('optionextended/template')->getOptionCount((int) Mage::registry('current_template')->getId());                          
    }

}
