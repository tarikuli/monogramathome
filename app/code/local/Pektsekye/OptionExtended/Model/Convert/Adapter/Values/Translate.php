<?php



class Pektsekye_OptionExtended_Model_Convert_Adapter_Values_Translate
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

        $optionRows = $read->fetchAssoc("SELECT code, option_id FROM {$resource->getTableName('optionextended/option')}");				
        $optionValueRows = array();
        $rs = $read->fetchAll("SELECT potv.option_id, oxv.row_id, ox_value_id, oxv.option_type_id FROM {$resource->getTableName('optionextended/value')} oxv, `{$resource->getTableName('catalog/product_option_type_value')}` potv WHERE oxv.option_type_id = potv.option_type_id");	
        foreach ($rs as $r) 
          $optionValueRows[$r['option_id']][$r['row_id']] = array('ox_value_id'=>$r['ox_value_id'],'option_type_id'=>$r['option_type_id']);

                    			
        $toProductOptionTypeTitleTable = "INSERT INTO `{$resource->getTableName('catalog/product_option_type_title')}` (`option_type_id`,`store_id`,`title`) VALUES ";
        $toOptionextendedValueDescriptionTable = "INSERT INTO `{$resource->getTableName('optionextended/value_description')}` (`ox_value_id`,`store_id`,`description`) VALUES ";
        
        
        $otIds  = array();
        $oxvIds = array();
        $storeIds = array();                
        $toPOTTT=$toOVDT='';

        
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
            
			      if (empty($d['row_id'])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" not defined', 'row_id'), Mage_Dataflow_Model_Convert_Exception::FATAL);           
              $skippedRows++;
              continue;
            }
                         
		        $rowId = (int) $d['row_id'];

			      if (!isset($optionValueRows[$optionId][$rowId])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, option value with row ID "%s" does not exist', $d['row_id']), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            }
            
            $optionTypeId = $optionValueRows[$optionId][$rowId]['option_type_id'];             
            $oxValueId = $optionValueRows[$optionId][$rowId]['ox_value_id'];             

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
                        
			      if (isset($storeIds[$optionTypeId][$storeId])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, option value with row_id "%s" and store "%s" for option code "%s" has been already imported', $d['row_id'], $d['store'], $d['option_code']), Mage_Dataflow_Model_Convert_Exception::FATAL);         
              $skippedRows++;
              continue;        
            }            
            $storeIds[$optionTypeId][$storeId] = 1;
            
            if ($storeId == 0)
              continue;        
          
            if ($d['title'] != '')
              $toPOTTT .= ($toPOTTT != '' ? ',' : '') . "({$optionTypeId},{$storeId},{$write->quote($d['title'])})";
            if ($d['description'] != '')              	                         
              $toOVDT  .= ($toOVDT != '' ? ',' : '') . "({$oxValueId},{$storeId},{$write->quote($d['description'])})"; 

            $otIds[$optionTypeId] = 1;
            $oxvIds[$oxValueId]   = 1; 
                                      
        }
                
        if (count($otIds) > 0){
          $write->raw_query("DELETE FROM `{$resource->getTableName('catalog/product_option_type_title')}` WHERE `option_type_id` IN (" . implode(',', array_keys($otIds)) .") AND `store_id` != 0 ");		    	
          $write->raw_query("DELETE FROM `{$resource->getTableName('optionextended/value_description')}` WHERE `ox_value_id` IN (" . implode(',', array_keys($oxvIds)) .") AND `store_id` != 0 ");	 	  	
          
          if ($toPOTTT != '')
	          $write->raw_query($toProductOptionTypeTitleTable . $toPOTTT);
          if ($toOVDT != '')			    											  
	          $write->raw_query($toOptionextendedValueDescriptionTable . $toOVDT); 
            
        } 
        
        $importedRows = $countRows - $skippedRows; 
                 
        if ($skippedRows == 0)     
          $this->addException(Mage::helper('optionextended')->__('Imported %d rows.',$countRows));
        else 
          $this->addException(Mage::helper('optionextended')->__('Imported %d rows of %d',$importedRows,$countRows));   


        return $this;

    }     
	
	
}
