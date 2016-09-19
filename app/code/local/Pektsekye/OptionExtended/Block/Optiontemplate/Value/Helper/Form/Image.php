<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Value_Helper_Form_Image extends Varien_Data_Form_Element_Abstract
{

    public function getElementHtml()
    {
        $html = $this->getContentHtml();
        //$html.= $this->getAfterElementHtml();
        return $html;
    }

    public function getContentHtml()
    {


        $content = Mage::getSingleton('core/layout')
            ->createBlock('optionextended/optiontemplate_value_helper_form_image_content');

        $content->setElement($this);
        
        return $content->toHtml();
    }

}
