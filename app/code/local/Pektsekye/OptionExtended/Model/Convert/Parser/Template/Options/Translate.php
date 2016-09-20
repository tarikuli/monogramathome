<?php



class Pektsekye_OptionExtended_Model_Convert_Parser_Template_Options_Translate extends Mage_Dataflow_Model_Convert_Parser_Csv
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
        SELECT oxto.code as option_code,cs.code,oxtot.title,oxton.note
        FROM `{$resource->getTableName('core/store')}` cs     
        JOIN `{$resource->getTableName('optionextended/template_option')}` oxto            
        LEFT JOIN  `{$resource->getTableName('optionextended/template_option_title')}` oxtot 
          ON oxtot.option_id = oxto.option_id AND oxtot.store_id = cs.store_id
        LEFT JOIN  `{$resource->getTableName('optionextended/template_option_note')}` oxton 
          ON oxton.option_id = oxto.option_id AND oxton.store_id = cs.store_id    
        ORDER BY oxto.code,cs.code                                                                                                  
      ");

      while ($row = $data->fetch())
        $io->write($this->getCsvString($row));	
		        
      $io->close();    

		  return $this;
		
	 }


}
