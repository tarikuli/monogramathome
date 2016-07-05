<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Grid_Renderer_Text extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $text = '';    
        $productIds = $this->getColumn()->getProductIds();
        if (isset($productIds[$row->getTemplateId()]))               
          $text  = $productIds[$row->getTemplateId()]['product_ids'];
          $count = $productIds[$row->getTemplateId()]['product_count'];
          
        if (strlen($text) > 20)
          $text = substr($text ,0,15) . '... (' . $count . ')';

        return $text;
    }

}
