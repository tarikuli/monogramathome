<?php 

class IWD_Opc_Model_Attribute_Option_HowFamiliarSocialMedia extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
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
                    'label' => Mage::helper('opc')->__('Very'),
                    'value' => 'Very'
                ),
                array(
                    'label' => Mage::helper('opc')->__('Somewhat'),
                    'value' => 'Somewhat'
                ),
                array(
                    'label' => Mage::helper('opc')->__('I am beginner'),
                    'value' => 'I am beginner'
                ),
                array(
                    'label' => Mage::helper('opc')->__('Not at all'),
                    'value' => 'Not at all'
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