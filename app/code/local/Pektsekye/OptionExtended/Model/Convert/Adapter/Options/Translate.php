<?php



class Pektsekye_OptionExtended_Model_Convert_Adapter_Options_Translate
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

        $stores = Mage::app()->getStores(true, true);
        
        $resource = Mage::getSingleton('core/resource'); 
        $read = $resource->getConnection('core_read');						
        $write = $resource->getConnection('core_write');

        $optionRows = $read->fetchAssoc("SELECT code, option_id, ox_option_id FROM {$resource->getTableName('optionextended/option')}");				

        $toProductOptionTitleTable = "INSERT INTO `{$resource->getTableName('catalog/product_option_title')}` (`option_id`,`store_id`,`title`) VALUES ";      
        $toOptionextendedOptionNoteTable = "INSERT INTO `{$resource->getTableName('optionextended/option_note')}` (`ox_option_id`,`store_id`,`note`) VALUES ";

        $oIds   = array();
        $oxoIds = array();
        $storeIds = array();        
        $toPOTT=$toOONT='';    
            
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

			      if (empty($d['option_code'])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" not defined', 'option_code'), Mage_Dataflow_Model_Convert_Exception::FATAL);           
              $skippedRows++;
              continue;
            }            

			      if (!isset($optionRows[$d['option_code']])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, option with code "%s" does not exist', $d['option_code']), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            }
            
            $optionId = $optionRows[$d['option_code']]['option_id'];             
            $oxOptionId = $optionRows[$d['option_code']]['ox_option_id'];


            if (empty($d['store'])) {
              $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" not defined', 'store'), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            }

            if (!isset($stores[$d['store']])) {
              $this->addException(Mage::helper('optionextended')->__('Skip import row, the store with code "%s" does not exist', $d['store']), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            }	
            
            $storeId = $stores[$d['store']]->getId();

			      if (isset($storeIds[$optionId][$storeId])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, option with code "%s" and store "%s" has been already imported', $d['option_code'], $d['store']), Mage_Dataflow_Model_Convert_Exception::FATAL);         
              $skippedRows++;
              continue;        
            }            
            $storeIds[$optionId][$storeId] = 1;

                        
            if ($storeId == 0)
              continue;  
              
            if ($d['title'] != '')
              $toPOTT .= ($toPOTT != '' ? ',' : '') . "({$optionId},{$storeId},{$write->quote($d['title'])})";
            if ($d['note'] != '')            	                         
              $toOONT .= ($toOONT != '' ? ',' : '') . "({$oxOptionId},{$storeId},{$write->quote($d['note'])})"; 

            $oIds[$optionId]     = 1;
            $oxoIds[$oxOptionId] = 1;
        }
        
        if (count($oIds) > 0){   
          $write->raw_query("DELETE FROM `{$resource->getTableName('catalog/product_option_title')}` WHERE `option_id` IN (" . implode(',', array_keys($oIds)) .") AND `store_id` != 0 ");		    	
          $write->raw_query("DELETE FROM `{$resource->getTableName('optionextended/option_note')}` WHERE `ox_option_id` IN (" . implode(',', array_keys($oxoIds)) .") AND `store_id` != 0 ");	 	  	

          if ($toPOTT != '')
			      $write->raw_query($toProductOptionTitleTable . $toPOTT);	
          if ($toOONT != '')			    										  
			      $write->raw_query($toOptionextendedOptionNoteTable . $toOONT); 			    	      		
					       	  	      
        }

        $importedRows = $countRows - $skippedRows;
          
        if ($skippedRows == 0)     
          $this->addException(Mage::helper('optionextended')->__('Imported %d rows.',$countRows));
        else 
          $this->addException(Mage::helper('optionextended')->__('Imported %d rows of %d',$importedRows,$countRows));   


        return $this;

    }
	
	
}
