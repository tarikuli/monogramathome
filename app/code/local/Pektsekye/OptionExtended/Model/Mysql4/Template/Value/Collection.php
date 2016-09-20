<?php

class Pektsekye_OptionExtended_Model_Mysql4_Template_Value_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
			parent::_construct();
        $this->_init('optionextended/template_value');
    }

    public function joinTitle($store_id)
    {
        $this->getSelect()
            ->join(array('default_value_title'=>$this->getTable('optionextended/template_value_title')),
                '`default_value_title`.value_id=`main_table`.value_id AND `default_value_title`.store_id='. (int) $store_id, 
                array('title'));

        return $this;
    }
    
    public function joinPrice($store_id)
    {
        $this->getSelect()
            ->join(array('default_value_price'=>$this->getTable('optionextended/template_value_price')),
                '`default_value_price`.value_id=`main_table`.value_id AND `default_value_price`.store_id='. (int) $store_id, 
                array('price' => 'FORMAT(price, 2)', 'price_type'));

        return $this;
    }

    public function joinDescription($store_id)
    {
        $this->getSelect()
            ->join(array('default_value_description'=>$this->getTable('optionextended/template_value_description')),
                '`default_value_description`.value_id=`main_table`.value_id AND `default_value_description`.store_id='. (int) $store_id, 
                array('description'));

        return $this;
    }

                          	 
}
