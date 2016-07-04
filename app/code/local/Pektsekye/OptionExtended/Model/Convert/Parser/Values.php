<?php



class Pektsekye_OptionExtended_Model_Convert_Parser_Values extends Mage_Dataflow_Model_Convert_Parser_Csv
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
          SELECT oxo.code,oxv.row_id,pott.title,potp.price,potp.price_type,potv.sku,potv.sort_order,oxv.children,oxv.image,oxvd.description  
          FROM `{$resource->getTableName('catalog/product_option')}` po 
          JOIN `{$resource->getTableName('optionextended/option')}` oxo 
            ON oxo.option_id = po.option_id 
          JOIN `{$resource->getTableName('catalog/product_option_type_value')}` potv 
            ON potv.option_id = po.option_id
          JOIN `{$resource->getTableName('optionextended/value')}` oxv 
            ON oxv.option_type_id = potv.option_type_id        
          LEFT JOIN  `{$resource->getTableName('catalog/product_option_type_title')}` pott 
            ON pott.option_type_id = potv.option_type_id AND pott.store_id = 0
          LEFT JOIN  `{$resource->getTableName('catalog/product_option_type_price')}` potp 
            ON potp.option_type_id = potv.option_type_id AND potp.store_id = 0
          LEFT JOIN  `{$resource->getTableName('optionextended/value_description')}` oxvd 
            ON oxvd.ox_value_id = oxv.ox_value_id AND oxvd.store_id = 0                                                    
      ");

      while ($row = $data->fetch())
        $io->write($this->getCsvString($row));					   

			$io->close();     

      return $this;
    
	 }


}
