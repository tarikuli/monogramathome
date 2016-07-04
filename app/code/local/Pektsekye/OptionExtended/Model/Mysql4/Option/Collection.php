<?php

class Pektsekye_OptionExtended_Model_Mysql4_Option_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
			parent::_construct();
        $this->_init('optionextended/option');
    }
	
	 public function joinNotes($storeId)
    {
        $this->getSelect()->joinLeft(array('option_note_default' => $this->getTable('optionextended/option_note')),
                '`main_table`.`ox_option_id` = `option_note_default`.`ox_option_id` and `option_note_default`.`store_id` = "0"',
                array())
            ->from('', array('default_note' => 'option_note_default.note'));

        if ($storeId !== null) {
            $this->getSelect()
                ->from('', array('store_note' => 'option_note.note', 'note' => 'IFNULL(`option_note`.`note`, `option_note_default`.`note`)'))
                ->joinLeft(array('option_note' => $this->getTable('optionextended/option_note')),
                    '`main_table`.`ox_option_id` = `option_note`.`ox_option_id` and `option_note`.`store_id` = "' . $storeId . '"',
                    array());
        }
        return $this;
    }
    
    public function joinTitles($store_id)
    {
        $this->getSelect()
            ->join(array('default_option_title'=>$this->getTable('catalog/product_option_title')),
                '`default_option_title`.option_id=`main_table`.option_id',
                array('default_title'=>'title'))
            ->joinLeft(array('store_option_title'=>$this->getTable('catalog/product_option_title')),
                '`store_option_title`.option_id=`main_table`.option_id AND '.$this->getConnection()->quoteInto('`store_option_title`.store_id=?', $store_id),
                array('store_title'=>'title',
                'title'=>new Zend_Db_Expr('IFNULL(`store_option_title`.title,`default_option_title`.title)')))
            ->where('`default_option_title`.store_id=?', 0);

        return $this;
    }

    public function joinPrices($store_id)
    {
        $this->getSelect()
            ->joinLeft(array('default_option_price'=>$this->getTable('catalog/product_option_price')),
                '`default_option_price`.option_id=`main_table`.option_id AND '.$this->getConnection()->quoteInto('`default_option_price`.store_id=?',0),
                array('default_price'=>'price','default_price_type'=>'price_type'))
            ->joinLeft(array('store_option_price'=>$this->getTable('catalog/product_option_price')),
                '`store_option_price`.option_id=`main_table`.option_id AND '.$this->getConnection()->quoteInto('`store_option_price`.store_id=?', $store_id),
                array('store_price'=>'price','store_price_type'=>'price_type',
                'price'=>new Zend_Db_Expr('IFNULL(`store_option_price`.price,`default_option_price`.price)'),
                'price_type'=>new Zend_Db_Expr('IFNULL(`store_option_price`.price_type,`default_option_price`.price_type)')));
        return $this;
    }   
    
    public function joinCoreOptionData()
    {
        $this->getSelect()
            ->join(array('core_option_table'=>$this->getTable('catalog/product_option')),
             '`core_option_table`.option_id=`main_table`.option_id');
        return $this;
    }  
    
    public function joinCoreProductSku()
    {
        $this->getSelect()
            ->join(array('core_product_table'=>$this->getTable('catalog/product')),
             '`core_product_table`.entity_id=`main_table`.product_id', array('product_sku' => 'sku'));
        return $this;
    }         
	 
}
