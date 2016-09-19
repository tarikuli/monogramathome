<?php



class Pektsekye_OptionExtended_Model_Convert_Parser_Templates extends Mage_Dataflow_Model_Convert_Parser_Csv
{

   public function unparse()
    {
        $io = $this->getBatchModel()->getIoAdapter();
        $io->open();
              
        $fieldList = array(				
		      'code',		    				
			    'title',
			    'is_active'	  					  			  			  		
        );
              
        $io->write($this->getCsvString($fieldList));
            
        $resource = Mage::getSingleton('core/resource'); 
        $read = $resource->getConnection('core_read');						
       
        $data = $read->query("
          SELECT code,title,is_active
          FROM `{$resource->getTableName('optionextended/template')}`                                                                                        
        ");

        while ($row = $data->fetch())
          $io->write($this->getCsvString($row));	

      $io->close();
      
      return $this;
	 }


}
