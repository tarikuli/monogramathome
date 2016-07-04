<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Value_Grid_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $image = $row->getData($this->getColumn()->getIndex());
        if ($image != ''){
          $product = Mage::getModel('catalog/product');
		      $url = $this->helper('catalog/image')->init($product, 'thumbnail', $image)->keepFrame(true)->resize(40,40)->__toString();
          return '<img src="'. $url .'" >';
        }
        return '';
    }

}
