<?php

class Pektsekye_OptionExtended_Model_Mysql4_Value extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('optionextended/value', 'ox_value_id');
    }
	
	    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
		  $read = $this->_getReadAdapter();
		  $write = $this->_getWriteAdapter();		 
		  $descriptionTable = $this->getTable('optionextended/value_description');		
		  
      if (!$object->getData('scope', 'optionextended_description')) {			
		    $statement = $read->select()
			    ->from($descriptionTable)
			    ->where('ox_value_id = '.$object->getId().' AND store_id = ?', 0);

		    if ($read->fetchOne($statement)) {
			    if ($object->getStoreId() == '0') {
				    $write->update(
					    $descriptionTable,
						    array('description' => $object->getDescription()),
						    $write->quoteInto('ox_value_id='.$object->getId().' AND store_id=?', 0)
				    );
			    }
		    } else {
			    $write->insert(
				    $descriptionTable,
					    array(
						    'ox_value_id' => $object->getId(),
						    'store_id' => 0,
						    'description' => $object->getDescription()
			    ));
		    }
      }

		  if ($object->getStoreId() != '0' && !$object->getData('scope', 'optionextended_description')) {
			  $statement = $read->select()
				  ->from($descriptionTable)
				  ->where('ox_value_id = '.$object->getId().' AND store_id = ?', $object->getStoreId());

			  if ($read->fetchOne($statement)) {;
				  $write->update(
					  $descriptionTable,
						  array('description' => $object->getDescription()),
						  $write->quoteInto('ox_value_id='.$object->getId().' AND store_id=?', $object->getStoreId()));
			  } else {
				  $write->insert(
					  $descriptionTable,
						  array(
							  'ox_value_id' => $object->getId(),
							  'store_id' => $object->getStoreId(),
							  'description' => $object->getDescription()
				  ));
			  }
		  }	elseif ($object->getData('scope', 'optionextended_description')){
        $write->delete(
            $descriptionTable,
            $write->quoteInto('ox_value_id = '.$object->getId().' AND store_id = ?', $object->getStoreId())
        );		  
		  }	
	}	
	
	
	 public function duplicate($oldOptionId, $newOptionId, $newProductId)
    {
         $read   = $this->_getReadAdapter();			
         $write  = $this->_getWriteAdapter();
			$productOptionValueTable = $this->getTable('catalog/product_option_type_value');
			$descriptionTable = $this->getTable('optionextended/value_description');				
				  
			$select = $read->select()
				->from($productOptionValueTable, 'option_type_id')
				->where('option_id=?', $oldOptionId);
			$oldTypeIds = $read->fetchCol($select);

			$select = $read->select()
				->from($productOptionValueTable, 'option_type_id')
				->where('option_id=?', $newOptionId);
			$newTypeIds = $read->fetchCol($select);

			foreach ($oldTypeIds as $ind => $oldTypeId) {
				
			// read and prepare original optionextended values
			  $select = $read->select()
					->from($this->getMainTable())
					->where('option_type_id=?', $oldTypeId);
				$row = $read->fetchRow($select);
				$oldOxValueId = $row['ox_value_id'];
				$row['option_type_id'] = $newTypeIds[$ind];						
				$row['product_id'] = $newProductId;				
				unset($row['ox_value_id']);

		  // insert optionextended values to duplicated option values
				$write->insert($this->getMainTable(), $row);
				$newOxValueId = $write->lastInsertId();

		  // copy optionextended values note
				$sql = 'REPLACE INTO `' . $descriptionTable . '` '
					 . 'SELECT NULL, ' . $newOxValueId . ', `store_id`, `description`'
					 . 'FROM `' . $descriptionTable . '` WHERE `ox_value_id`=' . $oldOxValueId;
				$write->query($sql);
						
			}
	 } 
	
}
