<?php



class Pektsekye_OptionExtended_Model_Convert_Adapter_Template_Values_Translate
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

        $optionIds = $read->fetchPairs("SELECT code, option_id FROM {$resource->getTableName('optionextended/template_option')}");
        				        				
        $valueIds = array();
        $rs = $read->fetchAll("SELECT option_id, row_id, value_id FROM {$resource->getTableName('optionextended/template_value')}");
        foreach ($rs as $r) 
          $valueIds[$r['option_id']][$r['row_id']] = $r['value_id'];
                    			
        $toOptionextendedTemplateValueTitleTable = "INSERT INTO `{$resource->getTableName('optionextended/template_value_title')}` (`value_id`,`store_id`,`title`) VALUES ";
        $toOptionextendedTemplateValueDescriptionTable = "INSERT INTO `{$resource->getTableName('optionextended/template_value_description')}` (`value_id`,`store_id`,`description`) VALUES ";        
        
        $vIds = array();
        $storeIds = array();                
        $toOTVTT=$toOTVDT='';

        
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

			      if (!isset($optionIds[$d['option_code']])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, option with code "%s" does not exist', $d['option_code']), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            }
            
            $optionId = $optionIds[$d['option_code']]; 
            
			      if (empty($d['row_id'])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" not defined', 'row_id'), Mage_Dataflow_Model_Convert_Exception::FATAL);           
              $skippedRows++;
              continue;
            }
                         
		        $rowId = (int) $d['row_id'];

			      if (!isset($valueIds[$optionId][$rowId])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, option value with row ID "%s" does not exist', $d['row_id']), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            }
          
            $valueId = $valueIds[$optionId][$rowId];             

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
                        
			      if (isset($storeIds[$valueId][$storeId])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, option value with row_id "%s" and store "%s" for option code "%s" has been already imported', $d['row_id'], $d['store'], $d['option_code']), Mage_Dataflow_Model_Convert_Exception::FATAL);         
              $skippedRows++;
              continue;        
            }            
            $storeIds[$valueId][$storeId] = 1;
            
            if ($storeId == 0)
              continue;        
          
            if ($d['title'] != '')
              $toOTVTT .= ($toOTVTT != '' ? ',' : '') . "({$valueId},{$storeId},{$write->quote($d['title'])})";
            if ($d['description'] != '')              	                         
              $toOTVDT  .= ($toOTVDT != '' ? ',' : '') . "({$valueId},{$storeId},{$write->quote($d['description'])})"; 

            $vIds[$valueId] = 1; 
                                      
        }
                
        if (count($vIds) > 0){
          $vIdsString = implode(',', array_keys($vIds));
          $write->raw_query("DELETE FROM `{$resource->getTableName('optionextended/template_value_title')}` WHERE `value_id` IN ({$vIdsString}) AND `store_id` != 0 ");		    	
          $write->raw_query("DELETE FROM `{$resource->getTableName('optionextended/template_value_description')}` WHERE `value_id` IN ({$vIdsString}) AND `store_id` != 0 ");	 	  	

          if ($toOTVTT != '')
	          $write->raw_query($toOptionextendedTemplateValueTitleTable . $toOTVTT);
          if ($toOTVDT != '')			    											  
	          $write->raw_query($toOptionextendedTemplateValueDescriptionTable . $toOTVDT); 
            
        } 
        
        $importedRows = $countRows - $skippedRows; 
                 
        if ($skippedRows == 0)     
          $this->addException(Mage::helper('optionextended')->__('Imported %d rows.',$countRows));
        else 
          $this->addException(Mage::helper('optionextended')->__('Imported %d rows of %d',$importedRows,$countRows));   


        return $this;

    }     
	
	
}
