<?php

class Pektsekye_OptionExtended_Helper_Data extends Mage_Core_Helper_Abstract
{



    public function getHiddenRequiredOptions($product, $requestOptions)
    {
					
			$children = array();		

      foreach ($product->getOptions() as $option){
        if (!is_null($option->getLayout())){
          
          if (!is_null($option->getRowId()))					
            $option_id_by_row_id[$option->getTemplateId()][(int) $option->getRowId()] = $option->getOptionId();
        
          if (!is_null($option->getValues())){			  			  
            foreach ($option->getValues() as $value) {
              $valueId = $value->getOptionTypeId();	                 			      		
              $value_id_by_row_id[$value->getTemplateId()][$value->getRowId()] = $valueId;                      
              $children[$value->getTemplateId()][$valueId] = explode(',', $value->getChildren());							
            }
          }		
        }		  						
      }			
    
    
      $options = Mage::getModel('optionextended/option')
        ->getCollection()		
        ->addFieldToFilter('product_id', $product->getId());		
      foreach ($options as $option){
        if (!is_null($option->getRowId()))					
          $option_id_by_row_id['orig'][(int) $option->getRowId()] = $option->getOptionId();		                            
      }	   
    
      $values = Mage::getModel('optionextended/value')
        ->getCollection()		
        ->addFieldToFilter('product_id', $product->getId());	
      foreach ($values as $value) {
        $valueId = $value->getOptionTypeId();							
        $value_id_by_row_id['orig'][$value->getRowId()] = $valueId;           
        $children['orig'][$valueId] = explode(',', $value->getChildren());									
      }						


      $oIdByVId = array();			
      foreach ($product->getOptions() as $option){
        if ($values = $option->getValues()){
          foreach ($values as $vId => $v)
            $oIdByVId[$vId] = $option->getId();
        }		  
      }
			
			$cOIdsByVId = array();	
			$cVIdsByVId = array();			
			$parentVIdByOId  = array();
      foreach ($children as $tId => $tc){						
        foreach ($tc as $valueId => $vc){
          foreach ($vc as $rId){
            if (isset($option_id_by_row_id[$tId][$rId])){
              $oId = (int) $option_id_by_row_id[$tId][$rId];
              $cOIdsByVId[$valueId][] = $oId;
              $parentVIdByOId[$oId] = $valueId;
            } elseif(isset($value_id_by_row_id[$tId][$rId])){
              $vId = (int) $value_id_by_row_id[$tId][$rId];		
              $cVIdsByVId[$valueId][] = $vId;
              $parentVIdByOId[$oIdByVId[$vId]] = $valueId;														
            }	
          }
        }
      }						
	
      $visibleOIds = array();	
      foreach ($requestOptions as $v){
        $vIds = is_array($v) ? $v : array($v);
        foreach ($vIds as $vId){
          if (isset($cOIdsByVId[$vId]))
            foreach ($cOIdsByVId[$vId] as $oId)          
              $visibleOIds[$oId] = 1;
          if (isset($cVIdsByVId[$vId]))
            foreach ($cVIdsByVId[$vId] as $id)
              $visibleOIds[$oIdByVId[$id]] = 1;                      
        }                 	  
      }
      	
      $hiddenOIds	= array();
      foreach ($product->getOptions() as $option){
        $oId = $option->getId();
        if ($option->getIsRequire() && isset($parentVIdByOId[$oId]) && !isset($visibleOIds[$oId])){
          $hiddenOIds[$oId]	= 1;
        }		  
      }	
	
			return $hiddenOIds;			
   
    }
    
    
}
