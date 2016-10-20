<?php 

class IWD_Opc_Model_Attribute_Option_WhatNeedsDreams extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' =>'',
                    'value' =>''
                ),
                array(
                    'label' => Mage::helper('opc')->__('Pay off my mortgage'),
                    'value' => 'Pay off my mortgage'
                ),
                array(
                    'label' => Mage::helper('opc')->__('Planning for college educations for my kids'),
                    'value' => 'Planning for college educations for my kids'
                ),
                array(
                    'label' => Mage::helper('opc')->__('Make ends meet'),
                    'value' => 'Make ends meet'
                ),
                array(
                    'label' => Mage::helper('opc')->__('Planning a memorable family vacation'),
                    'value' => 'Planning a memorable family vacation'
                ),
                array(
                    'label' => Mage::helper('opc')->__('I would like to become financially independent'),
                    'value' => 'I would like to become financially independent'
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

?>