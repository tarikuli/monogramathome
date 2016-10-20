<?php 

class IWD_Opc_Model_Attribute_Option_WhyAreYouInterested extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
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
                    'label' => Mage::helper('opc')->__('Opportunity to socialize with my friends and family'),
                    'value' => 'Opportunity to socialize with my friends and family'
                ),
                array(
                    'label' => Mage::helper('opc')->__('I would like to earn extra money to make ends meet'),
                    'value' => 'I would like to earn extra money to make ends meet'
                ),
                array(
                    'label' => Mage::helper('opc')->__('I want to be my own boss'),
                    'value' => 'I want to be my own boss'
                ),
                array(
                    'label' => Mage::helper('opc')->__('Need or Want to replace my salary'),
                    'value' => 'Need or Want to replace my salary'
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