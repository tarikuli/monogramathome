<?php



class Pektsekye_OptionExtended_Model_Convert_Parser_Template_Options extends Mage_Dataflow_Model_Convert_Parser_Csv
{

   public function unparse()
    {
        $io = $this->getBatchModel()->getIoAdapter();
        $io->open();
              
        $fieldList = array(				
		      'template_code',
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
          SELECT oxt.code as template_code,oxto.code,oxtot.title,oxto.type,oxto.is_require,oxto.sort_order,oxton.note,oxto.layout,oxto.popup,oxtop.price,oxtop.price_type,oxto.sku,oxto.max_characters,oxto.file_extension,oxto.image_size_x,oxto.image_size_y,oxto.row_id,oxto.selected_by_default
          FROM `{$resource->getTableName('optionextended/template')}` oxt
          JOIN `{$resource->getTableName('optionextended/template_option')}` oxto
            ON oxto.template_id = oxt.template_id            
          LEFT JOIN  `{$resource->getTableName('optionextended/template_option_title')}` oxtot 
            ON oxtot.option_id = oxto.option_id AND oxtot.store_id = 0
          LEFT JOIN  `{$resource->getTableName('optionextended/template_option_price')}` oxtop 
            ON oxtop.option_id = oxto.option_id AND oxtop.store_id = 0
          LEFT JOIN  `{$resource->getTableName('optionextended/template_option_note')}` oxton 
            ON oxton.option_id = oxto.option_id AND oxton.store_id = 0                                                                                         
        ");

        while ($row = $data->fetch())
          $io->write($this->getCsvString($row));	

      $io->close();
      
      return $this;
	 }


}
