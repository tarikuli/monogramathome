<?php



class Pektsekye_OptionExtended_Model_Convert_Parser_Products extends Mage_Dataflow_Model_Convert_Parser_Csv
{

   public function unparse()
    {
        $io = $this->getBatchModel()->getIoAdapter();
        $io->open();
              
        $fieldList = array(				
		      'product_sku',
		      'template_code'		  					  			  			  		
        );
              
        $io->write($this->getCsvString($fieldList));
            
        $resource = Mage::getSingleton('core/resource'); 
        $read = $resource->getConnection('core_read');						
       
        $data = $read->query("
          SELECT p.sku,oxt.code 
          FROM `{$resource->getTableName('optionextended/product_template')}` oxpt
          JOIN `{$resource->getTableName('catalog/product')}` p
            ON  p.entity_id = oxpt.product_id           
          JOIN `{$resource->getTableName('optionextended/template')}` oxt 
            ON oxt.template_id = oxpt.template_id
          ORDER BY p.sku,oxt.code                                                                                          
        ");

        while ($row = $data->fetch())
          $io->write($this->getCsvString($row));	

      $io->close();
      
      return $this;
	 }


}
