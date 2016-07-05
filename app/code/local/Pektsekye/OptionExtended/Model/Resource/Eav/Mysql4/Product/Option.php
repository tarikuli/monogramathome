<?php

class Pektsekye_OptionExtended_Model_Resource_Eav_Mysql4_Product_Option extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Option
{
    /**
     * Duplicate custom options for product
     *
     * @param Mage_Catalog_Model_Product_Option $object
     * @param int $oldProductId
     * @param int $newProductId
     * @return Mage_Catalog_Model_Product_Option
     */
    public function duplicate(Mage_Catalog_Model_Product_Option $object, $oldProductId, $newProductId)
    {
        Mage::getResourceModel('optionextended/template')->applyOptionTemplatesToDuplicatedProduct((int) $oldProductId, (int) $newProductId);								      

        $result = parent::duplicate($object, $oldProductId, $newProductId);	
		  
		
        $write  = $this->_getWriteAdapter();
        $read   = $this->_getReadAdapter();
		    $table = $this->getTable('optionextended/option');		  
		    $noteTable = $this->getTable('optionextended/option_note');
		    $ox_value_resource = Mage::getModel('optionextended/value')->getResource();
		  
        $r = $read->fetchRow("SHOW TABLE STATUS LIKE '{$table}'");
        $nextOxOptionId = $r['Auto_increment'];		  
        
        // read and prepare original product options
        $select = $read->select()
            ->from($this->getMainTable(), 'option_id')
            ->where('product_id=?', $oldProductId);
        $oldOptionIds = $read->fetchCol($select);

        $select = $read->select()
            ->from($this->getMainTable(), 'option_id')
            ->where('product_id=?', $newProductId);
        $newOptionIds = $read->fetchCol($select);
		  
        foreach ($oldOptionIds as $ind => $oldOptionId) {

			  // read and prepare original optionextended options
			    $select = $read->select()
					  ->from($table)
					  ->where('option_id=?', $oldOptionId);
				  $row = $read->fetchRow($select);
				  $oldOxOptionId =   $row['ox_option_id'];
				  $row['option_id'] = $newOptionIds[$ind];						
				  $row['product_id'] = $newProductId;
				  $row['code'] = "opt-{$newProductId}-{$nextOxOptionId}";				
				  unset($row['ox_option_id']);

		    // insert optionextended options to duplicated option
				  $write->insert($table, $row);

		    // copy optionextended options note
				  $sql = 'REPLACE INTO `' . $noteTable . '` '
					   . 'SELECT NULL, ' . $nextOxOptionId . ', `store_id`, `note`'
					   . 'FROM `' . $noteTable . '` WHERE `ox_option_id`=' . $oldOxOptionId;
				  $write->query($sql);
				
          $ox_value_resource->duplicate($oldOptionId, $newOptionIds[$ind], $newProductId);
          $nextOxOptionId++;
        }

        return $result;
    }

}
