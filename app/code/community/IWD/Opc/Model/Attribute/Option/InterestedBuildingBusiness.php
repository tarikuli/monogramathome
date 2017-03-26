<?php 

class IWD_Opc_Model_Attribute_Option_InterestedBuildingBusiness extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
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
                    'label' => Mage::helper('opc')->__('Just enough to make ends meet'),
                    'value' => 'Just enough to make ends meet'
                ),
                array(
                    'label' => Mage::helper('opc')->__('I would like to replace my salary within 3-6 months'),
                    'value' => 'I would like to replace my salary within 3-6 months'
                ),
                array(
                    'label' => Mage::helper('opc')->__('I am ready to get started building my business'),
                    'value' => 'I am ready to get started building my business'
                ),
                array(
                    'label' => Mage::helper('opc')->__('I am interested in creating enough money to secure my financial independence'),
                    'value' => 'I am interested in creating enough money to secure my financial independence'
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