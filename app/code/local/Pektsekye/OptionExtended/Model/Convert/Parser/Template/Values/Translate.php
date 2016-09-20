<?php



class Pektsekye_OptionExtended_Model_Convert_Parser_Template_Values_Translate extends Mage_Dataflow_Model_Convert_Parser_Csv
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
        SELECT oxto.code as option_code,oxtv.row_id,cs.code,oxtvt.title,oxtvd.description  
        FROM `{$resource->getTableName('core/store')}` cs
        JOIN `{$resource->getTableName('optionextended/template_option')}` oxto
        JOIN `{$resource->getTableName('optionextended/template_value')}` oxtv 
          ON oxtv.option_id = oxto.option_id                  
        LEFT JOIN  `{$resource->getTableName('optionextended/template_value_title')}` oxtvt 
          ON oxtvt.value_id = oxtv.value_id AND oxtvt.store_id = cs.store_id
        LEFT JOIN  `{$resource->getTableName('optionextended/template_value_description')}` oxtvd 
          ON oxtvd.value_id = oxtv.value_id AND oxtvd.store_id = cs.store_id
        ORDER BY oxto.code,oxtv.row_id,cs.code                                                                                                                    
      ");

      while ($row = $data->fetch())
        $io->write($this->getCsvString($row));
		        
      $io->close();    

		  return $this;
		
	 }


}
