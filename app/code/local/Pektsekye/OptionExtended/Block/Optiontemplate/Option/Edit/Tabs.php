<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Option_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('optionextended_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Option Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label'     => Mage::helper('adminhtml')->__('General Information'),
            'content'   => $this->getLayout()->createBlock('optionextended/optiontemplate_option_edit_tab_general')->toHtml(),
            'active'    => true
        ));
        
        $option = Mage::registry('current_option');
        $group  = Mage::getModel('catalog/product_option')->getGroupByType($option->getType());      
        if (!is_null($option->getId()) && $group == 'select'){
          $this->addTab('values', array(
              'label'     => $this->__('Values'),
              'content'   => $this->getLayout()->createBlock('optionextended/optiontemplate_option_edit_tab_values')->toHtml()         
          ));        
        }
        
        $this->_updateActiveTab();
        Varien_Profiler::stop('optionextended/tabs');
        return parent::_beforeToHtml();
    }

    protected function _updateActiveTab()
    {
        $tabId = $this->getRequest()->getParam('tab');
        if( $tabId ) {
            $tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
            if($tabId) {
                $this->setActiveTab($tabId);
            }
        }
    }
}
