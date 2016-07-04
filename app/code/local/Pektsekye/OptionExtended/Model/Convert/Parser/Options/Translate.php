<?php



class Pektsekye_OptionExtended_Model_Convert_Parser_Options_Translate extends Mage_Dataflow_Model_Convert_Parser_Csv
{	 
	 
   public function unparse()
    { 
    
      $io = $this->getBatchModel()->getIoAdapter();
      $io->open();
            
      $fieldList = array(				
		    'option_code',
		    'store',	
		    'title',
		    'note' 					  			  			  		
      );
            
      $io->write($this->getCsvString($fieldList));

      $resource = Mage::getSingleton('core/resource'); 
      $read = $resource->getConnection('core_read');						
     
      $data = $read->query("
        SELECT oxo.code as option_code,cs.code,pot.title,oxon.note
        FROM `{$resource->getTableName('core/store')}` cs     
        JOIN `{$resource->getTableName('optionextended/option')}` oxo            
        LEFT JOIN  `{$resource->getTableName('catalog/product_option_title')}` pot 
          ON pot.option_id = oxo.option_id AND pot.store_id = cs.store_id
        LEFT JOIN  `{$resource->getTableName('optionextended/option_note')}` oxon 
          ON oxon.ox_option_id = oxo.ox_option_id AND oxon.store_id = cs.store_id    
        ORDER BY oxo.code,cs.code                                                                                                  
      ");

      while ($row = $data->fetch())
        $io->write($this->getCsvString($row));	
		        
      $io->close();    

		  return $this;
		
	 }


}
