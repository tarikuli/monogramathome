<?php

class Pektsekye_OptionExtended_Model_Mysql4_Template_Value extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('optionextended/template_value', 'value_id');
    }

    
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
		    $read = $this->_getReadAdapter();
		    $write = $this->_getWriteAdapter();
        $titleTable = $this->getTable('optionextended/template_value_title');
        $priceTable = $this->getTable('optionextended/template_value_price');
		    $descriptionTable = $this->getTable('optionextended/template_value_description');
        


        //title
        if (is_null($object->getTitleUseDefault())) {
            $statement = $read->select()
                ->from($titleTable)
                ->where('value_id = '.$object->getId().' and store_id = ?', 0);

            if ($read->fetchOne($statement)) {
                if ($object->getStoreId() == '0') {
                    $write->update(
                        $titleTable,
                            array('title' => $object->getTitle()),
                            $write->quoteInto('value_id='.$object->getId().' AND store_id=?', 0)
                    );
                }
            } else {
                $write->insert(
                    $titleTable,
                        array(
                            'value_id' => $object->getId(),
                            'store_id' => 0,
                            'title' => $object->getTitle()
                ));
            }
        }

        if ($object->getStoreId() != '0' && is_null($object->getTitleUseDefault())) {
            $statement = $read->select()
                ->from($titleTable)
                ->where('value_id = '.$object->getId().' and store_id = ?', $object->getStoreId());

            if ($read->fetchOne($statement)) {
                $write->update(
                    $titleTable,
                        array('title' => $object->getTitle()),
                        $write
                            ->quoteInto('value_id='.$object->getId().' AND store_id=?', $object->getStoreId()));
            } else {
                $write->insert(
                    $titleTable,
                        array(
                            'value_id' => $object->getId(),
                            'store_id' => $object->getStoreId(),
                            'title' => $object->getTitle()
                ));
            }
        } elseif ($object->getTitleUseDefault() == 1) {
            $write->delete(
                $titleTable,
                $write->quoteInto('value_id = '.$object->getId().' AND store_id = ?', $object->getStoreId())
            );
        }



      // price
      if (is_null($object->getPriceUseDefault())) {
          $statement = $read->select()
              ->from($priceTable)
              ->where('value_id = '.$object->getId().' AND store_id = ?', 0);
          if ($read->fetchOne($statement)) {
              if ($object->getStoreId() == '0') {
                  $write->update(
                      $priceTable,
                      array(
                          'price' => $object->getPrice(),
                          'price_type' => $object->getPriceType()
                      ),
                      $write->quoteInto('value_id = '.$object->getId().' AND store_id = ?', 0)
                  );
              }
          } else {
              $write->insert(
                  $priceTable,
                  array(
                      'value_id' => $object->getId(),
                      'store_id' => 0,
                      'price' => $object->getPrice(),
                      'price_type' => $object->getPriceType()
                  )
              );
          }
      }

      if (Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE == (int) Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE)){

        if ($object->getStoreId() != '0' && is_null($object->getPriceUseDefault())) {

          $baseCurrency = Mage::app()->getBaseCurrencyCode();

          $storeIds = Mage::app()->getStore($object->getStoreId())->getWebsite()->getStoreIds();
          if (is_array($storeIds)) {
              foreach ($storeIds as $storeId) {
                  if ($object->getPriceType() == 'fixed') {
                      $storeCurrency = Mage::app()->getStore($storeId)->getBaseCurrencyCode();
                      $rate = Mage::getModel('directory/currency')->load($baseCurrency)->getRate($storeCurrency);
                      if (!$rate) {
                          $rate=1;
                      }
                      $newPrice = $object->getPrice() * $rate;
                  } else {
                      $newPrice = $object->getPrice();
                  }
                  $statement = $read->select()
                      ->from($priceTable)
                      ->where('value_id = '.$object->getId().' AND store_id = ?', $storeId);

                  if ($read->fetchOne($statement)) {
                      $write->update(
                          $priceTable,
                          array(
                              'price' => $newPrice,
                              'price_type' => $object->getPriceType()
                          ),
                          $write->quoteInto('value_id = '.$object->getId().' AND store_id = ?', $storeId)
                      );
                  } else {
                      $write->insert(
                          $priceTable,
                          array(
                              'value_id' => $object->getId(),
                              'store_id' => $storeId,
                              'price' => $newPrice,
                              'price_type' => $object->getPriceType()
                          )
                      );
                  }
              }// end foreach()
          }
        } elseif ($object->getPriceUseDefault() == 1) {
            $write->delete(
                $priceTable,
                $write->quoteInto('value_id = '.$object->getId().' AND store_id = ?', $object->getStoreId())
            );
        }              
      }



      // description		    		
        if (is_null($object->getDescriptionUseDefault())) {		
		      $statement = $read->select()
			      ->from($descriptionTable)
			      ->where('value_id = '.$object->getId().' AND store_id = ?', 0);

		      if ($read->fetchOne($statement)) {
			      if ($object->getStoreId() == '0') {
				      $write->update(
					      $descriptionTable,
						      array('description' => $object->getDescription()),
						      $write->quoteInto('value_id='.$object->getId().' AND store_id=?', 0)
				      );
			      }
		      } else {
			      $write->insert(
				      $descriptionTable,
					      array(
						      'value_id' => $object->getId(),
						      'store_id' => 0,
						      'description' => $object->getDescription()
			      ));
		      }
        }
        
		    if ($object->getStoreId() != '0' && is_null($object->getDescriptionUseDefault())) {
			    $statement = $read->select()
				    ->from($descriptionTable)
				    ->where('value_id = '.$object->getId().' AND store_id = ?', $object->getStoreId());

			    if ($read->fetchOne($statement)) {;
				    $write->update(
					    $descriptionTable,
						    array('description' => $object->getDescription()),
						    $write->quoteInto('value_id='.$object->getId().' AND store_id=?', $object->getStoreId()));
			    } else {
				    $write->insert(
					    $descriptionTable,
						    array(
							    'value_id' => $object->getId(),
							    'store_id' => $object->getStoreId(),
							    'description' => $object->getDescription()
				    ));
			    }
		    } elseif ($object->getDescriptionUseDefault() == 1){
            $write->delete(
                $descriptionTable,
                $write->quoteInto('value_id = '.$object->getId().' AND store_id = ?', $object->getStoreId())
            );		    
		    }


        return parent::_afterSave($object);
    }      


    public function getStoreFields($oxOptionId, $storeId)
    {

        $titleTable = $this->getTable('optionextended/template_value_title');
        $priceTable = $this->getTable('optionextended/template_value_price');
		    $descriptionTable = $this->getTable('optionextended/template_value_description');
    

        $select = $this->_getReadAdapter()->select()
            ->from(array('default_title_table'=>$titleTable),array())
            ->joinLeft(array('store_title_table'=>$titleTable),
                "store_title_table.value_id=default_title_table.value_id AND store_title_table.store_id={$storeId}",
                array('store_title' => 'title', 'title' => new Zend_Db_Expr('IFNULL(store_title_table.title, default_title_table.title)')))
                
            ->join(array('default_price_table' => $priceTable),
                "default_price_table.value_id=default_title_table.value_id AND default_price_table.store_id=0",array())
            ->joinLeft(array('store_price_table' => $priceTable),
                "store_price_table.value_id=default_price_table.value_id AND store_price_table.store_id={$storeId}",
                array('store_price' => 'price', 'price' => new Zend_Db_Expr('IFNULL(store_price_table.price, default_price_table.price)'), 'price_type' => new Zend_Db_Expr('IFNULL(store_price_table.price_type, default_price_table.price_type)')))
                
            ->join(array('default_description_table' => $descriptionTable),
                "default_description_table.value_id=default_title_table.value_id AND default_description_table.store_id=0",array())
            ->joinLeft(array('store_description_table' => $descriptionTable),
                "store_description_table.value_id=default_description_table.value_id AND store_description_table.store_id={$storeId}",
                array('store_description' => 'description', 'description' => new Zend_Db_Expr('IFNULL(store_description_table.description, default_description_table.description)')))                                
                
            ->where("default_title_table.value_id={$oxOptionId} AND default_title_table.store_id=0");    
            
        return $this->_getReadAdapter()->fetchRow($select);
    }


    public function deleteValuesWithChidrenUpdate($templateId, $ids, $rowIds = array())
    {
      if (!is_null($ids)){
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(),'row_id')
            ->where('value_id IN (?)', $ids);
        $rowIds = $this->_getReadAdapter()->fetchCol($select); 
      }
      
      $select = $this->_getReadAdapter()->select()
          ->from(array('option_table'=>$this->getTable('optionextended/template_option')),array())        
          ->join(array('main_table'=>$this->getMainTable()), 'main_table.option_id = option_table.option_id', array('value_id', 'children'))
          ->where('option_table.template_id = ?', $templateId);
      $rows = $this->_getReadAdapter()->fetchAll($select);   

      foreach ($rows as $row){
        $children = explode(',', $row['children']);
        $childrenNew = array_diff($children, $rowIds);
        if (count($children) != count($childrenNew))
          $this->_getWriteAdapter()->update($this->getMainTable(), array('children' => implode(',', $childrenNew)), 'value_id='.$row['value_id']);
      } 

      if (!is_null($ids))      
        $this->_getReadAdapter()->delete($this->getMainTable(), $this->_getWriteAdapter()->quoteInto('value_id IN (?)', $ids));                                 
    } 

    public function getNextId()
    {
        $r = $this->_getReadAdapter()->fetchRow("SHOW TABLE STATUS LIKE '{$this->getTable('optionextended/template_value')}'");
        
        return (int) $r['Auto_increment'];
    }   

}
