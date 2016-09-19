<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('optionextended_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Template Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label'     => Mage::helper('adminhtml')->__('General Information'),
            'content'   => $this->getLayout()->createBlock('optionextended/optiontemplate_edit_tab_general')->toHtml(),
            'active'    => true
        ));

        $this->addTab('products', array(
            'label'     => Mage::helper('adminhtml')->__('Products'),
            'content'   => $this->getLayout()->createBlock('optionextended/optiontemplate_edit_tab_products_grid')->toHtml()
        ));
        
        $template = Mage::registry('current_template');
        if (!is_null($template->getId())){
          $this->addTab('options', array(
              'label'     => Mage::helper('adminhtml')->__('Options'),
              'content'   => $this->getLayout()->createBlock('optionextended/optiontemplate_edit_tab_options')->toHtml()         
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
