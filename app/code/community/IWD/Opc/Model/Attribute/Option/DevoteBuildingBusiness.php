<?php 

class IWD_Opc_Model_Attribute_Option_DevoteBuildingBusiness extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
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
                    'label' => Mage::helper('opc')->__('2-3 hours per week'),
                    'value' => '2-3 hours per week'
                ),
                array(
                    'label' => Mage::helper('opc')->__('4-6 hours per week'),
                    'value' => '4-6 hours per week'
                ),
                array(
                    'label' => Mage::helper('opc')->__('7-10 hours per week'),
                    'value' => '7-10 hours per week'
                ),
                array(
                    'label' => Mage::helper('opc')->__('I will give this as much time as necessary'),
                    'value' => 'I will give this as much time as necessary'
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