<?php

/**
 * Class Julfiker_Party_ExtendedGrid_Block_Customer
 * @author: julfiker
 */
class Julfiker_Party_ExtendedGrid_Block_Customer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $id =  $row->getData($this->getColumn()->getIndex());
        $customer = Mage::getModel('customer/customer');
        $customer->load($id);
        return $customer->getName();
    }
}