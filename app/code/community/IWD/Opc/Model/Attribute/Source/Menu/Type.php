<?php

class IWD_Opc_Model_Attribute_Source_Menu_Type extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	const TYPE_BOTH = 0;
	const TYPE_MAIN_MENU = 1;
    const TYPE_AMBASSADOR_MENU = 2;
    const TYPE_AMBASSADOR_MEMBER_MENU = 3;
    const TYPE_AMBASSADOR_ONLY = 4;
    const TYPE_MAIN_MEMBER_MENU = 5;

	 public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => Mage::helper('opc')->__('All'),
                    'value' =>  self::TYPE_BOTH
                ),
                array(
                    'label' => Mage::helper('opc')->__('Main Site'),
                    'value' =>  self::TYPE_MAIN_MENU
                ),
                array(
                    'label' => Mage::helper('opc')->__('Main Site and (Member Only) Ambassador Site'),
                    'value' =>  self::TYPE_MAIN_MEMBER_MENU
                ),
                array(
                    'label' => Mage::helper('opc')->__('(Ambassador Only) Ambassador Site'),
                    'value' =>  self::TYPE_AMBASSADOR_ONLY
                ),
                array(
                    'label' => Mage::helper('opc')->__('(Member Only) Ambassador Site'),
                    'value' =>  self::TYPE_AMBASSADOR_MEMBER_MENU
                ),
                array(
                    'label' => Mage::helper('opc')->__('(All) Ambassador Site'),
                    'value' =>  self::TYPE_AMBASSADOR_MENU
                ),
            );
        }
        return $this->_options;
    }
 
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}