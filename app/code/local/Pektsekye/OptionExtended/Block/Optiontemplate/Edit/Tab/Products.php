<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Edit_Tab_Products extends  Mage_Adminhtml_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('optionextended/optiontemplate/edit/tab/products.phtml');
        $this->setId('optionextended_edit_tab_products');                             
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid',
            $this->getLayout()->createBlock('optionextended/optiontemplate_edit_tab_products_grid')     
        );
        
        return parent::_prepareLayout();
    }
          
           
    public function getProductGridUrl()
    {
        return $this->getUrl('adminhtml/optiontemplate/grid');
    }      


}
