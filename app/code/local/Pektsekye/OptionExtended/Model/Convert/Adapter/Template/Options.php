<?php



class Pektsekye_OptionExtended_Model_Convert_Adapter_Template_Options
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


        $types = array(
          Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD => 1,
		      Mage_Catalog_Model_Product_Option::OPTION_TYPE_AREA => 1,
		      Mage_Catalog_Model_Product_Option::OPTION_TYPE_FILE => 1,
		      Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN => 1,
		      Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO => 1,
		      Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX => 1,
		      Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE => 1,					
		      Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE => 1,
		      Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE_TIME => 1,
		      Mage_Catalog_Model_Product_Option::OPTION_TYPE_TIME => 1
	      );
	      
        $selectTypes = array(
		      Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN => 1,
		      Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO => 1,
		      Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX => 1,
		      Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE => 1,					
	      );        
        
        
        $layouts = array(
          Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO => array(
              'above'       =>1,        
              'before'      =>1,
              'below'       =>1,
              'swap'        =>1,
              'grid'        =>1,    
              'gridcompact' =>1,                  
              'list'        =>1               
            ),        
          Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX => array(
              'above'       =>1,         
              'below'       =>1,
              'grid'        =>1,  
              'gridcompact' =>1,                 
              'list'        =>1    
            ),        
          Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN => array(
              'above'     =>1,         
              'before'    =>1,
              'below'     =>1,
              'swap'      =>1,
              'picker'    =>1, 
              'pickerswap'=>1                 
            ),
          Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE => array(
              'above'=>1,        
              'below'=>1         
            )           
        );        
        
        $resource = Mage::getSingleton('core/resource'); 
        $read = $resource->getConnection('core_read');						
        $write = $resource->getConnection('core_write');

        $templateIds = $read->fetchPairs("SELECT `code`,`template_id` FROM {$resource->getTableName('optionextended/template')}");        	

        $r = $read->fetchRow("SHOW TABLE STATUS LIKE '{$resource->getTableName('optionextended/template_option')}'");
        $nextOptionId = $r['Auto_increment'];
				        
        $toOptionextendedTemplateOptionTable = "INSERT INTO `{$resource->getTableName('optionextended/template_option')}` (`option_id`,`template_id`,`code`,`row_id`,`type`,`is_require`,`sku`,`max_characters`,`file_extension`,`image_size_x`,`image_size_y`,`sort_order`,`layout`,`popup`,`selected_by_default`) VALUES ";              
        $toOptionextendedTemplateOptionTitleTable = "INSERT INTO `{$resource->getTableName('optionextended/template_option_title')}` (`option_id`,`title`) VALUES ";      
        $toOptionextendedTemplateOptionPriceTable = "INSERT INTO `{$resource->getTableName('optionextended/template_option_price')}` (`option_id`,`price`,`price_type`) VALUES ";        
        $toOptionextendedTemplateOptionNoteTable = "INSERT INTO `{$resource->getTableName('optionextended/template_option_note')}` (`option_id`,`note`) VALUES ";

        $importedCodes = array();
        $tIds = array();
        $rowIds = array(); 
        
        $toOTOT=$toOTOTT=$toOTOPT=$toOTONT='';       
                               
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
            
            if (empty($d['code'])){ 
              $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" not defined', 'code'), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            }
           
            if (isset($importedCodes[$d['code']])){ 
              $this->addException(Mage::helper('optionextended')->__('Skip import row, option with %s "%s" has been already imported', 'code', $d['code']), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            }    
                     
            $importedCodes[$d['code']] = 1;
            
            if (empty($d['title'])){ 
              $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" not defined', 'title'), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            } 
            if (empty($d['type'])){
              $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" not defined', 'type'), Mage_Dataflow_Model_Convert_Exception::FATAL);		
              $skippedRows++;
              continue;        
            } 	      						
            if (!isset($types[$d['type']])){ 	
	            $this->addException(Mage::helper('optionextended')->__('Skip import row, value "%s" is not valid for field "%s". Valid values for the field "%s" are: %s.' , $d['type'], 'type', 'type', implode(", ", array_keys($types))), Mage_Dataflow_Model_Convert_Exception::FATAL);
              $skippedRows++;
              continue;        
            }
            if (!isset($selectTypes[$d['type']])){           
			        if (empty($d['row_id'])){
                $this->addException(Mage::helper('optionextended')->__('Skip import row, required field "%s" of the option type "%s" is not defined', 'row_id', $d['type']), Mage_Dataflow_Model_Convert_Exception::FATAL);           
                $skippedRows++;
                continue;        
              }
			        if (isset($rowIds[$templateId][$d['row_id']])){
                $this->addException(Mage::helper('optionextended')->__('Skip import row, option with %s "%s" for template "%s" has been already imported', 'row_id', $d['row_id'], $d['template_code']), Mage_Dataflow_Model_Convert_Exception::FATAL);         
                $skippedRows++;
                continue;        
              }
              $rowIds[$templateId][$d['row_id']] = 1;              
            }            

                                     
            $type = $write->quote($d['type']); 
            $isRequire = (int) $d['is_require'];  
	          $sku = $write->quote($d['sku']);        
            $maxCharacters   = !empty($d['max_characters']) ? (int) $d['max_characters'] : 'NULL';
            $fileExtension   = !empty($d['file_extension']) ? $write->quote($d['file_extension']) : 'NULL';	          
            $imageSizeX      = (int) $d['image_size_x'];
            $imageSizeY      = (int) $d['image_size_y'];
	          $sortOrder = (int) $d['sort_order']; 
            $title = $write->quote($d['title']);		  
	          $price = $write->quote($d['price']);
	          $priceType = $write->quote($d['price_type']);
	          $code = $write->quote($d['code']);		   		        
            $rowId = !empty($d['row_id']) ? (int) $d['row_id'] : 'NULL';
            $layout = isset($layouts[$d['type']][$d['layout']]) ? $write->quote($d['layout']) : "'above'";
            $popup = $write->quote($d['popup']);
            $selectedByDeafault = $write->quote($d['selected_by_default']); 
            $note = $write->quote($d['note']);
            
	          $toOTOT  .= ($toOTOT != '' ? ',' : '') . "({$nextOptionId},{$templateId},{$code},{$rowId},{$type},{$isRequire},{$sku},{$maxCharacters},{$fileExtension},{$imageSizeX},{$imageSizeY},{$sortOrder},{$layout},{$popup},{$selectedByDeafault})";
            $toOTOTT .= ($toOTOTT != '' ? ',' : '') . "({$nextOptionId},{$title})";	       
            if (!isset($selectTypes[$d['type']]))              
              $toOTOPT .= ($toOTOPT != '' ? ',' : '') . "({$nextOptionId},{$price},{$priceType})";       
            $toOTONT .= ($toOTONT != '' ? ',' : '') . "({$nextOptionId},{$note})"; 
                           

            $tIds[$templateId] = 1;        
            $nextOptionId++;
        }
        
        $importedRows = $countRows - $skippedRows;

        if ($importedRows > 0){    
          $write->raw_query("DELETE FROM `{$resource->getTableName('optionextended/template_option')}` WHERE `template_id` IN (". implode(',', array_keys($tIds)) .")");		    	

          $codes = $read->fetchCol("SELECT `code` FROM {$resource->getTableName('optionextended/template_option')}");																
          $duplicateCodes = array_intersect(array_keys($importedCodes), $codes);
          
          if (count($duplicateCodes) > 0){ 
            $this->addException(Mage::helper('optionextended')->__('Option code(s) "%s" already exist. Stop import process.', implode(", ", $duplicateCodes)), Mage_Dataflow_Model_Convert_Exception::FATAL);
            $importedRows = 0;
            $skippedRows = $countRows;
          } else {

            $write->raw_query($toOptionextendedTemplateOptionTable . $toOTOT);
            $write->raw_query($toOptionextendedTemplateOptionTitleTable . $toOTOTT);						
            if ($toOTOPT != '')			
              $write->raw_query($toOptionextendedTemplateOptionPriceTable . $toOTOPT);			  
            $write->raw_query($toOptionextendedTemplateOptionNoteTable . $toOTONT); 	
            
          }  
        }
          
        if ($skippedRows == 0)     
          $this->addException(Mage::helper('optionextended')->__('Imported %d rows.',$countRows));
        else 
          $this->addException(Mage::helper('optionextended')->__('Imported %d rows of %d',$importedRows,$countRows));   

        return $this;

    }
	

	 
}
