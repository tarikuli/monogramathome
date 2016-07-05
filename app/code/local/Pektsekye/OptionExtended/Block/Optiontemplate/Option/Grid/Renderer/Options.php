<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Option_Grid_Renderer_Options extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{

    public function render(Varien_Object $row)
    {
        if (!is_null($row->getRowId()))
          return '';
          
        $options = $this->getColumn()->getOptions();
        if (!empty($options) && is_array($options)) {
            $value = $row->getData($this->getColumn()->getIndex());
            if (isset($options[$value]))
              return $options[$value];
        }
        
        return '';        
    }

}
