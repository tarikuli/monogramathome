<?php

class IWD_Opc_Model_System_Config_Source_Newsletter_Template 
{
    public function toOptionArray()
    {
        $newsletterTemplateCollection = Mage::getModel('newsletter/template')
            ->getCollection();

        $templateArray = array(array('value' => '', 'label' => Mage::helper('adminhtml')->__('[Please Select]')));
        foreach($newsletterTemplateCollection as $newsletterTemplate)
        {
            $templateArray[] = array('value' => $newsletterTemplate->getTemplateId(), 'label' => $newsletterTemplate->getTemplateCode());
        }

        return $templateArray;
    }
}
