<?php

class Pektsekye_OptionExtended_Model_Mysql4_Value_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
			parent::_construct();
        $this->_init('optionextended/value');
    }
	
	 public function joinDescriptions($storeId)
    {
        $this->getSelect()->joinLeft(array('value_description_default' => $this->getTable('optionextended/value_description')),
                '`main_table`.`ox_value_id` = `value_description_default`.`ox_value_id` and `value_description_default`.`store_id` = "0"',
                array())
            ->from('', array('default_description' => 'value_description_default.description'));

        if ($storeId !== null) {
            $this->getSelect()
                ->from('', array('store_description' => 'value_description.description', 'description' => 'IFNULL(`value_description`.`description`, `value_description_default`.`description`)'))
                ->joinLeft(array('value_description' => $this->getTable('optionextended/value_description')),
                    '`main_table`.`ox_value_id` = `value_description`.`ox_value_id` and `value_description`.`store_id` = "' . $storeId . '"',
                    array());
        }
        return $this;
    }
    

    public function joinTitles($store_id)
    {
        $this->getSelect()
            ->join(array('default_value_title'=>$this->getTable('catalog/product_option_type_title')),
                '`default_value_title`.option_type_id=`main_table`.option_type_id',
                array('default_title'=>'title'))
            ->joinLeft(array('store_value_title'=>$this->getTable('catalog/product_option_type_title')),
                '`store_value_title`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`store_value_title`.store_id=?',$store_id),
                array('store_title'=>'title','title'=>new Zend_Db_Expr('IFNULL(`store_value_title`.title,`default_value_title`.title)')))
            ->where('`default_value_title`.store_id=?',0);

        return $this;
    }


    public function joinPrices($store_id)
    {
        $this->getSelect()
            ->joinLeft(array('default_value_price'=>$this->getTable('catalog/product_option_type_price')),
                '`default_value_price`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`default_value_price`.store_id=?',0),
                array('default_price'=>'price','default_price_type'=>'price_type'))
            ->joinLeft(array('store_value_price'=>$this->getTable('catalog/product_option_type_price')),
                '`store_value_price`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`store_value_price`.store_id=?', $store_id),
                array('store_price'=>'price','store_price_type'=>'price_type',
                'price'=>new Zend_Db_Expr('IFNULL(`store_value_price`.price,`default_value_price`.price)'),
                'price_type'=>new Zend_Db_Expr('IFNULL(`store_value_price`.price_type,`default_value_price`.price_type)')));

        return $this;
    }
 
    
    public function joinCoreOptionValueData()
    {
        $this->getSelect()
            ->join(array('core_option_value_table'=>$this->getTable('catalog/product_option_type_value')),
             '`core_option_value_table`.option_type_id=`main_table`.option_type_id');
        return $this;
    } 
    
    
    public function joinOptionCodes()
    {
        $this->getSelect()
            ->join(array('optionextended_option_table'=>$this->getTable('optionextended/option')),
             '`optionextended_option_table`.option_id=`core_option_value_table`.option_id', array('option_code' => 'code'));
        return $this;
    }        
}
