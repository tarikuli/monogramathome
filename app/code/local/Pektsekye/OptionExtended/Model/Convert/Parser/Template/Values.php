<?php



class Pektsekye_OptionExtended_Model_Convert_Parser_Template_Values extends Mage_Dataflow_Model_Convert_Parser_Csv
{

	 
   public function unparse()
    {

      $io = $this->getBatchModel()->getIoAdapter();
      $io->open();
            
      $fieldList = array(				
        'option_code',
        'row_id',				  
        'title',
        'price',	
        'price_type',			  
        'sku',	
        'sort_order',					
        'children',		
        'image',
        'description'				  					  			  			  		
      );
            
      $io->write($this->getCsvString($fieldList));
          
      $resource = Mage::getSingleton('core/resource'); 
      $read = $resource->getConnection('core_read');						
     
      $data = $read->query("
          SELECT oxto.code,oxtv.row_id,oxtvt.title,oxtvp.price,oxtvp.price_type,oxtv.sku,oxtv.sort_order,oxtv.children,oxtv.image,oxtvd.description  
          FROM `{$resource->getTableName('optionextended/template_option')}` oxto 
          JOIN `{$resource->getTableName('optionextended/template_value')}` oxtv
            ON oxtv.option_id = oxto.option_id         
          LEFT JOIN  `{$resource->getTableName('optionextended/template_value_title')}` oxtvt 
            ON oxtvt.value_id = oxtv.value_id AND oxtvt.store_id = 0
          LEFT JOIN  `{$resource->getTableName('optionextended/template_value_price')}` oxtvp 
            ON oxtvp.value_id = oxtv.value_id AND oxtvp.store_id = 0
          LEFT JOIN  `{$resource->getTableName('optionextended/template_value_description')}` oxtvd 
            ON oxtvd.value_id = oxtv.value_id AND oxtvd.store_id = 0                                                    
      ");

      while ($row = $data->fetch())
        $io->write($this->getCsvString($row));					   

			$io->close();     

      return $this;
    
	 }


}
