<?php

class Pektsekye_OptionExtended_Model_Mysql4_Template_Option_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
			parent::_construct();
        $this->_init('optionextended/template_option');
    }

    public function joinTitle()
    {
        $this->getSelect()
            ->join(array('default_option_title'=>$this->getTable('optionextended/template_option_title')),
                '`default_option_title`.option_id=`main_table`.option_id AND `default_option_title`.store_id=0', 
                array('title'));

        return $this;
    }     
     
    public function joinPrice()
    {
        $this->getSelect()
            ->joinLeft(array('default_option_price'=>$this->getTable('optionextended/template_option_price')),
                '`default_option_price`.option_id=`main_table`.option_id AND `default_option_price`.store_id=0', 
                array('price' => 'FORMAT(price, 2)', 'price_type'));

        return $this;
    }  
      
    public function joinNote()
    {
        $this->getSelect()
            ->join(array('default_option_note'=>$this->getTable('optionextended/template_option_note')),
                '`default_option_note`.option_id=`main_table`.option_id AND `default_option_note`.store_id=0', 
                array('note'));

        return $this;
    } 
                  	 
}
