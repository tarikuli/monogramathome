<?php



class Pektsekye_OptionExtended_Model_Convert_Parser_Values_Translate extends Mage_Dataflow_Model_Convert_Parser_Csv
{	 
	 
   public function unparse()
    { 
    
      $io = $this->getBatchModel()->getIoAdapter();
      $io->open();
            
      $fieldList = array(				
		    'option_code',
		    'row_id',		    
		    'store',	
		    'title',
		    'description' 					  			  			  		
      );
            
      $io->write($this->getCsvString($fieldList));

      $resource = Mage::getSingleton('core/resource'); 
      $read = $resource->getConnection('core_read');						
     
      $data = $read->query("
        SELECT oxo.code as option_code,oxv.row_id,cs.code,pott.title,oxvd.description  
        FROM `{$resource->getTableName('core/store')}` cs
        JOIN `{$resource->getTableName('optionextended/option')}` oxo
        JOIN `{$resource->getTableName('catalog/product_option_type_value')}` potv 
          ON potv.option_id = oxo.option_id                  
        JOIN `{$resource->getTableName('optionextended/value')}` oxv 
          ON oxv.option_type_id = potv.option_type_id                  
        LEFT JOIN  `{$resource->getTableName('catalog/product_option_type_title')}` pott 
          ON pott.option_type_id = potv.option_type_id AND pott.store_id = cs.store_id
        LEFT JOIN  `{$resource->getTableName('optionextended/value_description')}` oxvd 
          ON oxvd.ox_value_id = oxv.ox_value_id AND oxvd.store_id = cs.store_id
        ORDER BY oxo.code,oxv.row_id,cs.code                                                                                                                    
      ");

      while ($row = $data->fetch())
        $io->write($this->getCsvString($row));
		        
      $io->close();    

		  return $this;
		
	 }


}
