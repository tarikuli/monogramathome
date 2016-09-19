<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Value_Helper_Form_Text extends Varien_Data_Form_Element_Text
{

    public function getAfterElementHtml()
    {
        $content = Mage::getSingleton('core/layout')
            ->createBlock('optionextended/optiontemplate_value_helper_form_text_content');
        
        return $content->toHtml();
    }
      
    public function getHtmlAttributes()
    {
        return array('type', 'title', 'class', 'style', 'onclick', 'onchange', 'onkeyup', 'onblur', 'disabled', 'readonly', 'maxlength', 'tabindex');
    }

}
