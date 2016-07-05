<?php



class Pektsekye_OptionExtended_Model_Convert_Adapter_Products
    extends Mage_Dataflow_Model_Convert_Parser_Csv
{


    public function parse()
    {

        setlocale(LC_ALL, Mage::app()->getLocale()->getLocaleCode().'.UTF-8');

        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');
        if ($fDel == '\t') {
            $fDel = "\t";
        }

        $batchModel = $this->getBatchModel();
        $batchIoAdapter = $this->getBatchModel()->getIoAdapter();

        $batchIoAdapter->open(false);

        $fieldNames = array();
        foreach ($batchIoAdapter->read(true, $fDel, $fEnc) as $v) {
            $fieldNames[$v] = $v;
        }
               
        $resource = Mage::getSingleton('core/resource'); 
        $read = $resource->getConnection('core_read');						
        $write = $resource->getConnection('core_write');

        $productIds = $read->fetchPairs("SELECT `sku`,`entity_id` FROM {$resource->getTableName('catalog/product')}");        
        $templateIds = $read->fetchPairs("SELECT `code`,`template_id` FROM {$resource->getTableName('optionextended/template')}");
        
        $toOptionextendedProductTemplateTable = "INSERT INTO `{$resource->getTableName('optionextended/product_template')}` (`product_id`,`template_id`) VALUES ";

        $toOPT='';
        $pIds = array();
                               
        $countRows = 0;
        $skippedRows = 0;        
        while (($csvData = $batchIoAdapter->read(true, $fDel, $fEnc)) !== false) {
            if (count($csvData) == 1 && $csvData[0] === null) {
                continue;
            }
            
            if ($skippedRows > 100){
						 $this->addException(Mage::helper('optionextended')->__('Too many rows to skip. Stop import process.'), Mage_Dataflow_Model_Convert_Exception::FATAL);
						 break;
						}
						
            $d = array();
            $countRows ++; $i = 0;
            foreach ($fieldNames as $field) {
                $d[$field] = isset($csvData[$i]) ? $csvData[$i] : null;
                $i ++;
            }


            if (empty($d['product_sku'])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" not defined', 'product_sku'), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            }    

			      if (!isset($productIds[$d['product_sku']])){      
              $this->addException(Mage::helper('optionextended')->__('Skip import row, the product with SKU "%s" does not exist', $d['product_sku']), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            } 
            
            $productId = $productIds[$d['product_sku']];                       
            
            if (empty($d['template_code'])){ 
              $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" not defined', 'template_code'), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            }
            
			      if (!isset($templateIds[$d['template_code']])){      
              $this->addException(Mage::helper('optionextended')->__('Skip import row, the template with code "%s" does not exist', $d['template_code']), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            } 
            
            $templateId = $templateIds[$d['template_code']];   
               
	          $toOPT  .= ($toOPT != '' ? ',' : '') . "({$productId},{$templateId})";
            $pIds[$productId] = 1;	          
        }
        
        $importedRows = $countRows - $skippedRows;

        if ($importedRows > 0){    
          $write->raw_query("DELETE FROM `{$resource->getTableName('optionextended/product_template')}` WHERE `product_id` IN (". implode(',', array_keys($pIds)) .")");		    	        	  	        	  	  	
			    $write->raw_query($toOptionextendedProductTemplateTable . $toOPT);		    			  	      
        }

          
        if ($skippedRows == 0)     
          $this->addException(Mage::helper('optionextended')->__('Imported %d rows.', $countRows));
        else 
          $this->addException(Mage::helper('optionextended')->__('Imported %d rows of %d', $importedRows, $countRows));   


        return $this;

    }
	

	 
}
