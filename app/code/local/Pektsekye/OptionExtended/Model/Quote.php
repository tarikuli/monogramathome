<?php

class Pektsekye_OptionExtended_Model_Quote extends Mage_Sales_Model_Quote
{

  
    public function addProductAdvanced(Mage_Catalog_Model_Product $product, $request = null, $processMode = null)
    {
		  if ($product->getRequiredOptions() && $request != null && isset($request['options'])){
        $hasSelected = false;
        foreach ($request['options'] as $v){
          if (!empty($v)){
            $hasSelected = true;
            break;
          }                 	  
        }
        if ($hasSelected){
          $hiddenOIds = Mage::helper('optionextended')->getHiddenRequiredOptions($product, $request['options']);  		  		
          foreach ($product->getOptions() as $option){
            if (isset($hiddenOIds[$option->getId()])) 
              $option->setIsRequire(false);		  
          }
				}   
		  }
		  
		  return parent::addProductAdvanced($product, $request, $processMode);    
    }

}
