<?php

class IWD_Opc_Model_System_Config_Source_Website 
{
    public function toOptionArray($isActiveOnlyFlag=false)
    {
        $websiteCollection = Mage::getModel('core/website')->getCollection();

        $websiteArray = array();
        foreach($websiteCollection as $website)
        {
            $websiteArray[] = array('value' => $website->getId(), 'label' => $website->getName());
        }

        return $websiteArray;
    }
}
