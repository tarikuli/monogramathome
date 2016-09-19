<?php



class Pektsekye_OptionExtended_Model_Convert_Parser_Options extends Mage_Dataflow_Model_Convert_Parser_Csv
{

   public function unparse()
    {
        $io = $this->getBatchModel()->getIoAdapter();
        $io->open();
              
        $fieldList = array(				
		      'product_sku',
		      'code',		    				
			    'title',
			    'type',
			    'is_require',
			    'sort_order',				
			    'note',	
			    'layout',			
			    'popup',						
			    'price',	
			    'price_type',	
			    'sku',
			    'max_characters',
			    'file_extension',
			    'image_size_x',
			    'image_size_y',			  		
			    'row_id',
			    'selected_by_default'		  					  			  			  		
        );
              
        $io->write($this->getCsvString($fieldList));
            
        $resource = Mage::getSingleton('core/resource'); 
        $read = $resource->getConnection('core_read');						
       
        $data = $read->query("
          SELECT p.sku as product_sku,oxo.code,pot.title,po.type,po.is_require,po.sort_order,oxon.note,oxo.layout,oxo.popup,pop.price,pop.price_type,po.sku,po.max_characters,po.file_extension,po.image_size_x,po.image_size_y,oxo.row_id,oxo.selected_by_default
          FROM `{$resource->getTableName('catalog/product')}` p
          JOIN `{$resource->getTableName('catalog/product_option')}` po
            ON po.product_id = p.entity_id            
          JOIN `{$resource->getTableName('optionextended/option')}` oxo 
            ON oxo.option_id = po.option_id 
          LEFT JOIN  `{$resource->getTableName('catalog/product_option_title')}` pot 
            ON pot.option_id = po.option_id AND pot.store_id = 0
          LEFT JOIN  `{$resource->getTableName('catalog/product_option_price')}` pop 
            ON pop.option_id = po.option_id AND pop.store_id = 0
          LEFT JOIN  `{$resource->getTableName('optionextended/option_note')}` oxon 
            ON oxon.ox_option_id = oxo.ox_option_id AND oxon.store_id = 0                                                                                         
        ");

        while ($row = $data->fetch())
          $io->write($this->getCsvString($row));	

      $io->close();
      
      return $this;
	 }


}
