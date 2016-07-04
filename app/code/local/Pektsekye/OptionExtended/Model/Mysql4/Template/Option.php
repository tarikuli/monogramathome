<?php

class Pektsekye_OptionExtended_Model_Mysql4_Template_Option extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('optionextended/template_option', 'option_id');
    }
    
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(array(
            'field' => 'code',
            'title' => Mage::helper('optionextended')->__('Option with the same code')
        ));
        return $this;
    }

    
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
		    $read = $this->_getReadAdapter();
		    $write = $this->_getWriteAdapter();
        $titleTable = $this->getTable('optionextended/template_option_title');
        $priceTable = $this->getTable('optionextended/template_option_price');
		    $noteTable = $this->getTable('optionextended/template_option_note');
        


        //title
        if (is_null($object->getTitleUseDefault())) {
            $statement = $read->select()
                ->from($titleTable)
                ->where('option_id = '.$object->getId().' and store_id = ?', 0);

            if ($read->fetchOne($statement)) {
                if ($object->getStoreId() == '0') {
                    $write->update(
                        $titleTable,
                            array('title' => $object->getTitle()),
                            $write->quoteInto('option_id='.$object->getId().' AND store_id=?', 0)
                    );
                }
            } else {
                $write->insert(
                    $titleTable,
                        array(
                            'option_id' => $object->getId(),
                            'store_id' => 0,
                            'title' => $object->getTitle()
                ));
            }
        }

        if ($object->getStoreId() != '0' && is_null($object->getTitleUseDefault())) {
            $statement = $read->select()
                ->from($titleTable)
                ->where('option_id = '.$object->getId().' and store_id = ?', $object->getStoreId());

            if ($read->fetchOne($statement)) {
                $write->update(
                    $titleTable,
                        array('title' => $object->getTitle()),
                        $write
                            ->quoteInto('option_id='.$object->getId().' AND store_id=?', $object->getStoreId()));
            } else {
                $write->insert(
                    $titleTable,
                        array(
                            'option_id' => $object->getId(),
                            'store_id' => $object->getStoreId(),
                            'title' => $object->getTitle()
                ));
            }
        } elseif ($object->getTitleUseDefault() == 1) {
            $write->delete(
                $titleTable,
                $write->quoteInto('option_id = '.$object->getId().' AND store_id = ?', $object->getStoreId())
            );
        }



      // price
        if ($object->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD
            || $object->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_AREA
            || $object->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_FILE
            || $object->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE
            || $object->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE_TIME
            || $object->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_TIME
        ) {

            //save for store_id = 0
            if (is_null($object->getPriceUseDefault())) {
                $statement = $read->select()
                    ->from($priceTable)
                    ->where('option_id = '.$object->getId().' AND store_id = ?', 0);
                if ($read->fetchOne($statement)) {
                    if ($object->getStoreId() == '0') {
                        $write->update(
                            $priceTable,
                            array(
                                'price' => $object->getPrice(),
                                'price_type' => $object->getPriceType()
                            ),
                            $write->quoteInto('option_id = '.$object->getId().' AND store_id = ?', 0)
                        );
                    }
                } else {
                    $write->insert(
                        $priceTable,
                        array(
                            'option_id' => $object->getId(),
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
                            ->where('option_id = '.$object->getId().' AND store_id = ?', $storeId);

                        if ($read->fetchOne($statement)) {
                            $write->update(
                                $priceTable,
                                array(
                                    'price' => $newPrice,
                                    'price_type' => $object->getPriceType()
                                ),
                                $write->quoteInto('option_id = '.$object->getId().' AND store_id = ?', $storeId)
                            );
                        } else {
                            $write->insert(
                                $priceTable,
                                array(
                                    'option_id' => $object->getId(),
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
                      $write->quoteInto('option_id = '.$object->getId().' AND store_id = ?', $object->getStoreId())
                  );
              }              
            }
        }


      // note		    		
        if (is_null($object->getNoteUseDefault())) {		
		      $statement = $read->select()
			      ->from($noteTable)
			      ->where('option_id = '.$object->getId().' AND store_id = ?', 0);

		      if ($read->fetchOne($statement)) {
			      if ($object->getStoreId() == '0') {
				      $write->update(
					      $noteTable,
						      array('note' => $object->getNote()),
						      $write->quoteInto('option_id='.$object->getId().' AND store_id=?', 0)
				      );
			      }
		      } else {
			      $write->insert(
				      $noteTable,
					      array(
						      'option_id' => $object->getId(),
						      'store_id' => 0,
						      'note' => $object->getNote()
			      ));
		      }
        }
        
		    if ($object->getStoreId() != '0' && is_null($object->getNoteUseDefault())) {
			    $statement = $read->select()
				    ->from($noteTable)
				    ->where('option_id = '.$object->getId().' AND store_id = ?', $object->getStoreId());

			    if ($read->fetchOne($statement)) {;
				    $write->update(
					    $noteTable,
						    array('note' => $object->getNote()),
						    $write->quoteInto('option_id='.$object->getId().' AND store_id=?', $object->getStoreId()));
			    } else {
				    $write->insert(
					    $noteTable,
						    array(
							    'option_id' => $object->getId(),
							    'store_id' => $object->getStoreId(),
							    'note' => $object->getNote()
				    ));
			    }
		    } elseif ($object->getNoteUseDefault() == 1){
            $write->delete(
                $noteTable,
                $write->quoteInto('option_id = '.$object->getId().' AND store_id = ?', $object->getStoreId())
            );		    
		    }


        return parent::_afterSave($object);
    }      


    public function getStoreFields($oxOptionId, $storeId)
    {

        $titleTable = $this->getTable('optionextended/template_option_title');
        $priceTable = $this->getTable('optionextended/template_option_price');
		    $noteTable = $this->getTable('optionextended/template_option_note');
    

        $select = $this->_getReadAdapter()->select()
            ->from(array('default_title_table'=>$titleTable),array())
            ->joinLeft(array('store_title_table'=>$titleTable),
                "store_title_table.option_id=default_title_table.option_id AND store_title_table.store_id={$storeId}",
                array('store_title' => 'title', 'title' => new Zend_Db_Expr('IFNULL(store_title_table.title, default_title_table.title)')))
                
            ->joinLeft(array('default_price_table' => $priceTable),
                "default_price_table.option_id=default_title_table.option_id AND default_price_table.store_id=0",array())
            ->joinLeft(array('store_price_table' => $priceTable),
                "store_price_table.option_id=default_price_table.option_id AND store_price_table.store_id={$storeId}",
                array('store_price' => 'price', 'price' => new Zend_Db_Expr('IFNULL(store_price_table.price, default_price_table.price)'), 'price_type' => new Zend_Db_Expr('IFNULL(store_price_table.price_type, default_price_table.price_type)')))
                
            ->join(array('default_note_table' => $noteTable),
                "default_note_table.option_id=default_title_table.option_id AND default_note_table.store_id=0",array())
            ->joinLeft(array('store_note_table' => $noteTable),
                "store_note_table.option_id=default_note_table.option_id AND store_note_table.store_id={$storeId}",
                array('store_note' => 'note', 'note' => new Zend_Db_Expr('IFNULL(store_note_table.note, default_note_table.note)')))                                
                
            ->where("default_title_table.option_id={$oxOptionId} AND default_title_table.store_id=0");    
            
        return $this->_getReadAdapter()->fetchRow($select);
    }

    
    public function getLastRowId($templateId)
    {
          $id = (int) $this->_getReadAdapter()->fetchOne("
            SELECT MAX(row_id) as last_row_id 
            FROM  `{$this->getTable('optionextended/template_option')}`       
            WHERE template_id={$templateId} AND `type` IN ('field','area','file','date','date_time','time')   
            GROUP BY template_id
          ");

          $idV = (int) $this->_getReadAdapter()->fetchOne("
            SELECT MAX(oxtv.row_id) as last_row_id 
            FROM  `{$this->getTable('optionextended/template_option')}` oxto
            JOIN  `{$this->getTable('optionextended/template_value')}`  oxtv
              ON  oxtv.option_id = oxto.option_id
            WHERE template_id={$templateId}  
            GROUP BY template_id
          ");
          
      return max($id, $idV);
   }


    public function getNextId()
    {
        $r = $this->_getReadAdapter()->fetchRow("SHOW TABLE STATUS LIKE '{$this->getTable('optionextended/template_option')}'");
        
        return (int) $r['Auto_increment'];
    }

    
    public function getChildrenOptionData($templateId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('option_table'=>$this->getTable('optionextended/template_option')), array('option_id', 'row_id'))        
            ->join(array('option_title_table'=>$this->getTable('optionextended/template_option_title')),
             "option_title_table.option_id=option_table.option_id AND option_title_table.store_id=0",
              array('title'))                
            ->where("option_table.template_id = {$templateId}")
            ->order('sort_order', 'title');                 
            
        return $this->_getReadAdapter()->fetchAll($select);
   }




    public function getChildrenValueData($templateId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('option_table'=>$this->getTable('optionextended/template_option')), array('option_id'))                     
            ->join(array('value_table'=>$this->getTable('optionextended/template_value')),
                "value_table.option_id=option_table.option_id ", array('value_id', 'row_id', 'children'))            
            ->join(array('value_title_table'=>$this->getTable('optionextended/template_value_title')),
                "value_title_table.value_id=value_table.value_id AND value_title_table.store_id=0",
                array('title'))
                
            ->where("option_table.template_id = {$templateId}")
            ->order('value_table.sort_order', 'title');                  
            
        return $this->_getReadAdapter()->fetchAll($select);
   }



   
    public function getValueTitles($optionId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('value_table'=>$this->getTable('optionextended/template_value')), array('row_id'))            
            ->join(array('value_title_table'=>$this->getTable('optionextended/template_value_title')),
                "value_title_table.value_id=value_table.value_id AND value_title_table.store_id=0",
                array('title'))                
            ->where("value_table.option_id = {$optionId}")    
            ->order('sort_order', 'title'); 
                       
        return $this->_getReadAdapter()->fetchAll($select);
   }


  public function deleteOptionsWithChidrenUpdate($templateId, $ids)
  {    
    $select = $this->_getReadAdapter()->select()
        ->from($this->getMainTable(), 'row_id')       
        ->where("type IN ('field','area','file','date','date_time','time') AND option_id IN (?)", $ids);
    $rowIds = $this->_getReadAdapter()->fetchCol($select);
        
    $select = $this->_getReadAdapter()->select()
        ->from($this->getTable('optionextended/template_value'), 'row_id')
        ->where('option_id IN (?)', $ids);
    $rowIds = array_merge($rowIds, $this->_getReadAdapter()->fetchCol($select));

    Mage::getResourceModel('optionextended/template_value')->deleteValuesWithChidrenUpdate((int) $templateId, null, $rowIds);

    $this->_getReadAdapter()->delete($this->getMainTable(), $this->_getWriteAdapter()->quoteInto('option_id IN (?)', $ids));       
  } 


   public function deleteValuesWithChidrenUpdate($templateId, $optionId)
  {
    $select = $this->_getReadAdapter()->select()
        ->from($this->getMainTable(), 'row_id')       
        ->where("type IN ('field','area','file','date','date_time','time') AND option_id = ?", $optionId);
    $rowIds = $this->_getReadAdapter()->fetchCol($select);
        
    $select = $this->_getReadAdapter()->select()
        ->from($this->getTable('optionextended/template_value'), 'row_id')
        ->where('option_id = ?', $optionId);
    $rowIds = array_merge($rowIds, $this->_getReadAdapter()->fetchCol($select));

    Mage::getResourceModel('optionextended/template_value')->deleteValuesWithChidrenUpdate((int) $templateId, null, $rowIds);
  }  
  
  
   public function deletePrice($optionId)
  {
    $this->_getWriteAdapter()->delete(
      $this->getTable('optionextended/template_option_price'),
      $this->_getWriteAdapter()->quoteInto('option_id = ?', $optionId)
    );  
  }   




  
   public function importOptionsFromProduct($templateId, $productId)
  {
	
		  $read   = $this->_getReadAdapter();		  
		  $write  = $this->_getWriteAdapter();
		  
		  $cpT    = $this->getTable('catalog/product');
		  $cpoT   = $this->getTable('catalog/product_option');
		  $oxoT   = $this->getTable('optionextended/option');
		  $csT    = $this->getTable('core/store');
		  $cpotT  = $this->getTable('catalog/product_option_title');
		  $cpopT  = $this->getTable('catalog/product_option_price');
		  $oxonT  = $this->getTable('optionextended/option_note');
      $cpotvT = $this->getTable('catalog/product_option_type_value');
      $cpottT = $this->getTable('catalog/product_option_type_title');
      $cpotpT = $this->getTable('catalog/product_option_type_price');
      $oxvT   = $this->getTable('optionextended/value');
      $oxvdT  = $this->getTable('optionextended/value_description'); 
           		  	     			
      $oxtoT   = $this->getTable('optionextended/template_option');
      $oxtotT  = $this->getTable('optionextended/template_option_title');
      $oxtopT  = $this->getTable('optionextended/template_option_price');
      $oxtonT  = $this->getTable('optionextended/template_option_note');                   
      $oxtvT   = $this->getTable('optionextended/template_value'); 
      $oxtvtT  = $this->getTable('optionextended/template_value_title');
      $oxtvpT  = $this->getTable('optionextended/template_value_price');
      $oxtvdT  = $this->getTable('optionextended/template_value_description');

             			
      $oResult = $read->fetchAll("
        SELECT po.option_id,type,is_require,sku,max_characters,file_extension,image_size_x,image_size_y,sort_order,
               row_id,layout,popup,selected_by_default
        FROM `{$cpoT}` po 
        JOIN `{$oxoT}` oxo
          ON oxo.option_id = po.option_id
        WHERE po.product_id={$productId}    
      ");
      
      $options = array();
      foreach ($oResult as $r){ 
        $maxCharacters   = !is_null($r['max_characters']) ? $r['max_characters'] : 'NULL';
        $fileExtension   = !is_null($r['file_extension']) ? $write->quote($r['file_extension']) : 'NULL';	          
        $imageSizeX      = (int) $r['image_size_x'];
        $imageSizeY      = (int) $r['image_size_y'];
        $rowId           = !is_null($r['row_id']) ? $r['row_id'] : 'NULL';               
        $options[$r['option_id']]  = "{$rowId},'{$r['type']}',{$r['is_require']},{$write->quote($r['sku'])},{$maxCharacters},{$fileExtension},{$imageSizeX},{$imageSizeY},{$r['sort_order']},'{$r['layout']}','{$r['popup']}','{$r['selected_by_default']}')"; 
      }     
      unset($oResult);
 
	    if (count($options) > 0){
          
        $otResult = $read->fetchAll("
          SELECT po.option_id,cs.store_id,title,price,price_type,note
          FROM `{$cpoT}` po 
          JOIN `{$oxoT}` oxo 
            ON oxo.option_id = po.option_id
          JOIN `{$csT}` cs  
          LEFT JOIN  `{$cpotT}` pot 
            ON pot.option_id = po.option_id AND pot.store_id = cs.store_id
          LEFT JOIN  `{$cpopT}` pop 
            ON pop.option_id = po.option_id AND pop.store_id = cs.store_id
          LEFT JOIN  `{$oxonT}` oxon 
            ON oxon.ox_option_id = oxo.ox_option_id AND oxon.store_id = cs.store_id                           
          WHERE po.product_id={$productId}    
        ");

        $oTitles = array();
        $oPrices = array();
        $oNotes = array();

        foreach ($otResult as $r){
         if (!is_null($r['title']))     
          $oTitles[$r['option_id']][] = array('store_id'=>$r['store_id'], 'title'=>$write->quote($r['title']));
         if (!is_null($r['price']))     
          $oPrices[$r['option_id']][] = array('store_id'=>$r['store_id'], 'price'=>$write->quote($r['price']), 'price_type'=>$r['price_type']); 
         if (!is_null($r['note']))     
          $oNotes[$r['option_id']][] = array('store_id'=>$r['store_id'], 'note'=>$write->quote($r['note']));                      
        }  
        unset($otResult);

        $ovResult = $read->fetchAll("
          SELECT po.option_id,potv.option_type_id,potv.sku,potv.sort_order,
                 oxv.row_id,children,image
          FROM `{$cpoT}` po 
          JOIN `{$oxoT}` oxo ON oxo.option_id = po.option_id 
          JOIN `{$cpotvT}` potv ON potv.option_id = po.option_id
          JOIN `{$oxvT}` oxv ON oxv.option_type_id = potv.option_type_id                                  
          WHERE po.product_id={$productId}    
        ");
   
        $values = array();      
        foreach ($ovResult as $r)
          $values[$r['option_id']][$r['option_type_id']] = "{$r['row_id']},{$write->quote($r['sku'])},{$r['sort_order']},{$write->quote($r['children'])},{$write->quote($r['image'])})";           
                       
        unset($ovResult);
        
        $ovtResult = $read->fetchAll("
          SELECT potv.option_type_id,po.option_id,cs.store_id,title,price,price_type,description
          FROM `{$cpoT}` po 
          JOIN `{$oxoT}` oxo 
            ON oxo.option_id = po.option_id 
          JOIN `{$cpotvT}` potv 
            ON potv.option_id = po.option_id
          JOIN `{$oxvT}` oxv 
            ON oxv.option_type_id = potv.option_type_id 
          JOIN  `{$csT}` cs        
          LEFT JOIN  `{$cpottT}` pott 
            ON pott.option_type_id = potv.option_type_id AND pott.store_id = cs.store_id
          LEFT JOIN  `{$cpotpT}` potp 
            ON potp.option_type_id = potv.option_type_id AND potp.store_id = cs.store_id
          LEFT JOIN  `{$oxvdT}` oxvd 
            ON oxvd.ox_value_id = oxv.ox_value_id AND oxvd.store_id = cs.store_id                                                
          WHERE po.product_id={$productId}
        ");
        
        $ovTitles = array();
        $ovPrices = array();
        $ovDescriptions = array(); 
            
        foreach ($ovtResult as $r){
         if (!is_null($r['title']))     
          $ovTitles[$r['option_type_id']][] = "{$r['store_id']},{$write->quote($r['title'])})";
         if (!is_null($r['price']))     
          $ovPrices[$r['option_type_id']][] = "{$r['store_id']},{$r['price']},'{$r['price_type']}')";
         if (!is_null($r['description']))     
          $ovDescriptions[$r['option_type_id']][] = "{$r['store_id']},{$write->quote($r['description'])})";                               
        }
        unset($ovtResult);        
        

        $r = $read->fetchRow("SHOW TABLE STATUS LIKE '{$oxtoT}'");
        $nextOptionId = $r['Auto_increment'];
        $r = $read->fetchRow("SHOW TABLE STATUS LIKE '{$oxtvT}'");
        $nextValueId = $r['Auto_increment'];	

        $toOptionTable           = "INSERT INTO `{$oxtoT}`  (`option_id`,`template_id`,`code`,`row_id`,`type`,`is_require`,`sku`,`max_characters`,`file_extension`,`image_size_x`,`image_size_y`,`sort_order`,`layout`,`popup`,`selected_by_default`) VALUES	";      
        $toOptionTitleTable      = "INSERT INTO `{$oxtotT}` (`option_id`,`store_id`,`title`) VALUES	";      
        $toOptionPriceTable      = "INSERT INTO `{$oxtopT}` (`option_id`,`store_id`,`price`,`price_type`) VALUES ";
        $toOptionNoteTable       = "INSERT INTO `{$oxtonT}` (`option_id`,`store_id`,`note`) VALUES ";
        $toValueTable            = "INSERT INTO `{$oxtvT}`  (`value_id`,`option_id`,`row_id`,`sku`,`sort_order`,`children`,`image`) VALUES ";
        $toValueTitleTable       = "INSERT INTO `{$oxtvtT}` (`value_id`,`store_id`,`title`) VALUES ";
        $toValuePriceTable       = "INSERT INTO `{$oxtvpT}` (`value_id`,`store_id`,`price`,`price_type`) VALUES ";
        $toValueDescriptionTable = "INSERT INTO `{$oxtvdT}` (`value_id`,`store_id`,`description`) VALUES ";


        $toOT=$toOTT=$toOPT=$toONT=$toVT=$toVTT=$toVPT=$toVDT='';
       
        $haveOptionPrices = $haveOptionValues = false; 


        foreach($options as $id => $r){	 
     
          $toOT .= ($toOT != '' ? ',' : '') . "({$nextOptionId},{$templateId},'opt-{$templateId}-{$nextOptionId}',{$r}";            
          foreach ($oTitles[$id] as $k => $v)
            $toOTT .= ($toOTT != '' ? ',' : '') . "({$nextOptionId},{$v['store_id']},{$v['title']})";
          if (isset($oPrices[$id])){
            foreach ($oPrices[$id] as $k => $v)              	      
              $toOPT .= ($toOPT != '' ? ',' : '') . "({$nextOptionId},{$v['store_id']},{$v['price']},'{$v['price_type']}')";
            $haveOptionPrices = true;  
          }        
          foreach ($oNotes[$id] as $k => $v)                                     
            $toONT .= ($toONT != '' ? ',' : '') . "({$nextOptionId},{$v['store_id']},{$v['note']})";
                              
          if (isset($values[$id])){           
            foreach ($values[$id] as $k => $v){	              
              $toVT .= ($toVT != '' ? ',' : '') . "({$nextValueId},{$nextOptionId},{$v}";
              foreach ($ovTitles[$k] as $vv)                
                $toVTT .= ($toVTT != '' ?',' : '')  . "({$nextValueId},{$vv}";                 
              foreach ($ovPrices[$k] as $vv)                   
                $toVPT .= ($toVPT != '' ? ',' : '') . "({$nextValueId},{$vv}";                
              foreach ($ovDescriptions[$k] as $vv)                  
                $toVDT .= ($toVDT != '' ? ',' : '') . "({$nextValueId},{$vv}";                                                             
              $nextValueId++;	    	    		      	  
	          }           	
	          $haveOptionValues = true;
          }
		                    
          $nextOptionId++;	
        }	  

        $write->raw_query($toOptionTable . $toOT);
        $write->raw_query($toOptionTitleTable . $toOTT);
        if ($haveOptionPrices)	              
          $write->raw_query($toOptionPriceTable . $toOPT);
        $write->raw_query($toOptionNoteTable . $toONT);
                    
        if ($haveOptionValues){         
          $write->raw_query($toValueTable . $toVT);
          $write->raw_query($toValueTitleTable . $toVTT);
          $write->raw_query($toValuePriceTable . $toVPT);
          $write->raw_query($toValueDescriptionTable . $toVDT);
        }				
     
    } 
    
  }   














   public function duplicate($optionId)
  {

      $toOT=$toOTT=$toOPT=$toONT=$toVT=$toVTT=$toVPT=$toVDT='';
      $haveOptionPrices = $haveOptionValues = false; 
      $newRowIds = array();
           
		  $read   = $this->_getReadAdapter();		  
		  $write  = $this->_getWriteAdapter();
		  
		  $csT    = $this->getTable('core/store');
           		  	     			
      $oxtoT   = $this->getTable('optionextended/template_option');
      $oxtotT  = $this->getTable('optionextended/template_option_title');
      $oxtopT  = $this->getTable('optionextended/template_option_price');
      $oxtonT  = $this->getTable('optionextended/template_option_note');                   
      $oxtvT   = $this->getTable('optionextended/template_value'); 
      $oxtvtT  = $this->getTable('optionextended/template_value_title');
      $oxtvpT  = $this->getTable('optionextended/template_value_price');
      $oxtvdT  = $this->getTable('optionextended/template_value_description');
      
      $r = $read->fetchRow("SHOW TABLE STATUS LIKE '{$oxtoT}'");
      $nextOptionId = $r['Auto_increment'];
      $r = $read->fetchRow("SHOW TABLE STATUS LIKE '{$oxtvT}'");
      $nextValueId = $r['Auto_increment'];	
                     			
      $oResult = $read->fetchRow("
        SELECT option_id,template_id,type,is_require,sku,max_characters,file_extension,image_size_x,image_size_y,sort_order,
               row_id,layout,popup,selected_by_default
        FROM `{$oxtoT}`
        WHERE option_id={$optionId}    
      ");
      
      if (!empty($oResult)){

        $templateId = (int) $oResult['template_id'];       
        $lastRowId  = $this->getLastRowId($templateId);
                  
        $otResult = $read->fetchAll("
          SELECT cs.store_id,title,price,price_type,note
          FROM `{$oxtoT}` oxt 
          JOIN `{$csT}` cs     
          LEFT JOIN  `{$oxtotT}` oxtt 
            ON oxtt.option_id = oxt.option_id AND oxtt.store_id = cs.store_id
          LEFT JOIN  `{$oxtopT}` oxtp 
            ON oxtp.option_id = oxt.option_id AND oxtp.store_id = cs.store_id
          LEFT JOIN  `{$oxtonT}` oxtn 
            ON oxtn.option_id = oxt.option_id AND oxtn.store_id = cs.store_id                           
          WHERE oxt.option_id={$optionId}    
        ");

        foreach ($otResult as $r){
         if (!is_null($r['title']))     
          $toOTT .= ($toOTT != '' ? ',' : '') . "({$nextOptionId},{$r['store_id']},{$write->quote($r['title'])})";
         if (!is_null($r['price'])){     
          $toOPT .= ($toOPT != '' ? ',' : '') . "({$nextOptionId},{$r['store_id']},'{$r['price']}','{$r['price_type']}')"; 
          $haveOptionPrices = true;
         } 
         if (!is_null($r['note']))     
          $toONT .= ($toONT != '' ? ',' : '') . "({$nextOptionId},{$r['store_id']},{$write->quote($r['note'])})";                      
        }
       
        unset($otResult);

        $ovResult = $read->fetchAll("
          SELECT option_id,value_id,sku,sort_order,
                 row_id,children,image
          FROM `{$oxtvT}`                                 
          WHERE option_id={$optionId}    
        ");
   
        $values = array();      
        foreach ($ovResult as $r){
          $rowId = $lastRowId + 1;
          $newRowIds[$r['row_id']] = $rowId;           
          $values[$r['value_id']] = "{$rowId},{$write->quote($r['sku'])},{$r['sort_order']},{$write->quote($r['image'])})";           
          $lastRowId++;
        }            
        unset($ovResult);
    
        $vResult = $read->fetchAll("
          SELECT v.value_id,v.option_id,cs.store_id,title,price,price_type,description
          FROM `{$oxtvT}` v 
          JOIN  `{$csT}` cs        
          LEFT JOIN  `{$oxtvtT}` vt 
            ON vt.value_id = v.value_id AND vt.store_id = cs.store_id
          LEFT JOIN  `{$oxtvpT}` vp 
            ON vp.value_id = v.value_id AND vp.store_id = cs.store_id
          LEFT JOIN  `{$oxtvdT}` vd 
            ON vd.value_id = v.value_id AND vd.store_id = cs.store_id                                                
          WHERE v.option_id={$optionId}
        ");
   
        $ovTitles = array();
        $ovPrices = array();
        $ovDescriptions = array(); 
            
        foreach ($vResult as $r){
         if (!is_null($r['title']))     
          $ovTitles[$r['value_id']][] = "{$r['store_id']},{$write->quote($r['title'])})";
         if (!is_null($r['price']))     
          $ovPrices[$r['value_id']][] = "{$r['store_id']},{$r['price']},'{$r['price_type']}')";
         if (!is_null($r['description']))     
          $ovDescriptions[$r['value_id']][] = "{$r['store_id']},{$write->quote($r['description'])})";                               
        }
        unset($vResult);


        $maxCharacters   = !is_null($oResult['max_characters']) ? $oResult['max_characters'] : 'NULL';
        $fileExtension   = !is_null($oResult['file_extension']) ? $write->quote($oResult['file_extension']) : 'NULL';	          
        $imageSizeX      = (int) $oResult['image_size_x'];
        $imageSizeY      = (int) $oResult['image_size_y'];
     
        $rowId = 'NULL';
        if (!is_null($oResult['row_id'])){
          $rowId = $lastRowId + 1;
          $newRowIds[$oResult['row_id']] = $rowId;
          $lastRowId++;
        }  

        $sd = '';
        if ($oResult['selected_by_default'] != '')        
          foreach (explode(',', $oResult['selected_by_default']) as $id)
            $sd .= ($sd != '' ? ',' : '') . $newRowIds[$id];
                                                  
        $toOT = "({$nextOptionId},{$templateId},'opt-{$templateId}-{$nextOptionId}',{$rowId},'{$oResult['type']}',{$oResult['is_require']},{$write->quote($oResult['sku'])},{$maxCharacters},{$fileExtension},{$imageSizeX},{$imageSizeY},{$oResult['sort_order']},'{$oResult['layout']}','{$oResult['popup']}','{$sd}')";        
        unset($oResult);
                                        
        if (count($values) > 0){           
          foreach ($values as $k => $v){	              
            $toVT .= ($toVT != '' ? ',' : '') . "({$nextValueId},{$nextOptionId},{$v}";
            foreach ($ovTitles[$k] as $vv)                
              $toVTT .= ($toVTT != '' ?',' : '')  . "({$nextValueId},{$vv}";                 
            foreach ($ovPrices[$k] as $vv)                   
              $toVPT .= ($toVPT != '' ? ',' : '') . "({$nextValueId},{$vv}";                
            foreach ($ovDescriptions[$k] as $vv)                  
              $toVDT .= ($toVDT != '' ? ',' : '') . "({$nextValueId},{$vv}";                                                             
            $nextValueId++;	    	    		      	  
          }           	
          $haveOptionValues = true;
        }
		                      
        $toOptionTable           = "INSERT INTO `{$oxtoT}`  (`option_id`,`template_id`,`code`,`row_id`,`type`,`is_require`,`sku`,`max_characters`,`file_extension`,`image_size_x`,`image_size_y`,`sort_order`,`layout`,`popup`,`selected_by_default`) VALUES	";      
        $toOptionTitleTable      = "INSERT INTO `{$oxtotT}` (`option_id`,`store_id`,`title`) VALUES	";      
        $toOptionPriceTable      = "INSERT INTO `{$oxtopT}` (`option_id`,`store_id`,`price`,`price_type`) VALUES ";
        $toOptionNoteTable       = "INSERT INTO `{$oxtonT}` (`option_id`,`store_id`,`note`) VALUES ";
        $toValueTable            = "INSERT INTO `{$oxtvT}`  (`value_id`,`option_id`,`row_id`,`sku`,`sort_order`,`image`) VALUES ";
        $toValueTitleTable       = "INSERT INTO `{$oxtvtT}` (`value_id`,`store_id`,`title`) VALUES ";
        $toValuePriceTable       = "INSERT INTO `{$oxtvpT}` (`value_id`,`store_id`,`price`,`price_type`) VALUES ";
        $toValueDescriptionTable = "INSERT INTO `{$oxtvdT}` (`value_id`,`store_id`,`description`) VALUES ";

        $write->raw_query($toOptionTable . $toOT);
        $write->raw_query($toOptionTitleTable . $toOTT);
        if ($haveOptionPrices)	              
          $write->raw_query($toOptionPriceTable . $toOPT);
        $write->raw_query($toOptionNoteTable . $toONT);
                    
        if ($haveOptionValues){         
          $write->raw_query($toValueTable . $toVT);
          $write->raw_query($toValueTitleTable . $toVTT);
          $write->raw_query($toValuePriceTable . $toVPT);
          $write->raw_query($toValueDescriptionTable . $toVDT);
        }				
     
    } 
    
    return $nextOptionId;
 
  }      

  public function getValueCount($optionId)
  {
      $select = $this->_getReadAdapter()
          ->select()         
          ->from($this->getTable('optionextended/template_value'), 'COUNT(value_id)')
          ->where('option_id = ?', $optionId)
          ->group('option_id');
      return $this->_getReadAdapter()->fetchOne($select);   

  } 
     
  public function getGridValueCount($templateId)
  {
      $select = $this->_getReadAdapter()
          ->select()
          ->from(array('option_table'=>$this->getTable('optionextended/template_option')), 'option_id')            
          ->join(array('value_table'=>$this->getTable('optionextended/template_value')), 
            'value_table.option_id = option_table.option_id',
            'COUNT(value_id)')
          ->where('template_id = ?', $templateId)
          ->group('option_table.option_id');
      return $this->_getReadAdapter()->fetchPairs($select);   

  } 
    
}
