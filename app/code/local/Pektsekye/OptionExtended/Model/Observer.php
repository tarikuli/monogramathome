<?php

class Pektsekye_OptionExtended_Model_Observer extends Mage_Core_Model_Abstract
{

  protected $_option_id = null;
  protected $_product_id = null;
  
  
	public function addOptionTemplatesToProduct(Varien_Event_Observer $observer)
	{
    $product = $observer->getEvent()->getProduct();
    $this->_addOptionTemplatesToProduct($product); 
    
	}	


	public function addOptionTemplatesToProductCollection(Varien_Event_Observer $observer)
	{
    $addOptions = false;
    foreach (debug_backtrace(false) as $step){
	    if ($step['function'] == 'addOptionsToResult' || (isset($step['class']) && $step['class'] == 'Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid' && $step['function'] == '_prepareCollection')){
	      $addOptions = true;
	      break;	      
	    }  
    }

	  if ($addOptions){
      $collection = $observer->getEvent()->getCollection();     
        foreach ($collection as $item)
          $this->_addOptionTemplatesToProduct($item); 
    }     
	}	

		
	public function productSaveBefore(Varien_Event_Observer $observer)
	{
    $inst = $observer->getEvent()->getProduct()->getOptionInstance();
    $options = $inst->getOptions();
    foreach ($options as $k => $option){      
      if (!isset($option['values']))
       $options[$k]['values'] = array();
    }          
    $inst->setOptions($options);	
	}



	public function productSaveAfter(Varien_Event_Observer $observer)
	{		
		$product   = $observer->getEvent()->getProduct();
		$idsString = $product->getOptionextendedTemplateIds();	

		if (!is_null($idsString)){		
		  $templateIds = $idsString != '' ? explode(',', $idsString) : array();
      Mage::getResourceModel('optionextended/template')->updateProductTemplates($product->getId(), $templateIds);								
    }
	}

	
	
	public function optionSaveAfter(Varien_Event_Observer $observer)
	{
	
		$object = $observer->getEvent()->getObject();
		$resource_name = $object->getResourceName();
		
		if ($resource_name == 'catalog/product_option'){

			$model = Mage::getModel('optionextended/option');
			$collection = $model->getCollection()->addFieldToFilter('option_id', $object->getId());
			if ($item = $collection->getFirstItem())	
				$model->setId($item['ox_option_id']);
			
			$code = $object->getCode() != '' ? $object->getCode() : 'opt-'. $object->getProductId() .'-'. $object->getId();
			
			$model->setStoreId($object->getStoreId());
			$model->setOptionId($object->getId());	
			$model->setProductId($object->getProductId());
			$model->setScope($object->getScope());			
			$model->setRowId($object->getRowId());
			$model->setNote($object->getNote());			
			$model->setLayout($object->getLayout());
			$model->setPopup((int) $object->getPopup());
			$model->setCode($code);			
			$model->setSelectedByDefault((string) $object->getSelectedByDefault());				
			$model->save();	
		
		} elseif ($resource_name == 'catalog/product_option_value'){
			
			$ox_value_id = null;
			$model = Mage::getModel('optionextended/value');
			$collection = $model->getCollection()->addFieldToFilter('option_type_id', $object->getId());
			if ($item = $collection->getFirstItem())
				$ox_value_id = $item['ox_value_id'];
				
			$model->setStoreId($object->getStoreId());
			$model->setId($ox_value_id);	
			$model->setOptionTypeId($object->getId());			
			if ($object->getProduct())				
				$model->setProductId($object->getProduct()->getId());		
			else 
				$model->setProductId($object->getOption()->getProductId());
			$model->setScope($object->getScope());				
			$model->setRowId((int) $object->getRowId());
			$model->setChildren((string) $object->getChildren());
      $duplicate = false;							
			$image = '';
			if ($object->getImage() != '') {
				if (strpos($object->getImage(), '{') !== false){				
					$imageInfo = Zend_Json::decode($object->getImage());
					$duplicate = isset($imageInfo['toduplicate']);
					if (isset($imageInfo['savedas'])){
						$image = $imageInfo['savedas'];
					} elseif (isset($imageInfo['file'])){		
						$image = $this->_moveImageFromTmp($imageInfo['file'], $duplicate);
					}	
				}	else {
					$image = $object->getImage();
				}
			}				
			if (isset($imageInfo['file']) || $duplicate || !isset($imageInfo['url'])){
				$model->setImage($image);
			}			
			$model->setDescription((string) $object->getDescription());				
			$model->save();
		}

		
  }
	 
	 
	public function setSkipCheckRequired(Varien_Event_Observer $observer)
	{ 
		$observer->getEvent()->getProduct()->setSkipCheckRequiredOption(true);	
	}
	
	
  public function setSkipCheckRequiredToCollection($observer)
  {
      $productCollection = $observer->getEvent()->getProductCollection();
      foreach ($productCollection as $product){
        foreach ($product->getOptions() as $option) 
				  $option->setIsRequire(false);
      }

  }
    
    
    /**
     * Move image from temporary directory to normal
     *
     * @param string $file
     * @return string
     */
    protected function _moveImageFromTmp($file, $duplicate)
    {

        $ioObject = new Varien_Io_File();
        $destDirectory = dirname($this->_getMadiaConfig()->getMediaPath($file));

        try {
            $ioObject->open(array('path'=>$destDirectory));
        } catch (Exception $e) {
            $ioObject->mkdir($destDirectory, 0777, true);
            $ioObject->open(array('path'=>$destDirectory));
        }

        if (strrpos($file, '.tmp') == strlen($file)-4) {
            $file = substr($file, 0, strlen($file)-4);
        }

        $destFile = dirname($file) . $ioObject->dirsep()
                  . Varien_File_Uploader::getNewFileName($this->_getMadiaConfig()->getMediaPath($file));
			if ($duplicate){
			  $ioObject->cp(
					$this->_getMadiaConfig()->getTmpMediaPath($file),
					$this->_getMadiaConfig()->getMediaPath($destFile)
			  );
			} else {
			  $ioObject->mv(
					$this->_getMadiaConfig()->getTmpMediaPath($file),
					$this->_getMadiaConfig()->getMediaPath($destFile)
			  );	
			}	
        return str_replace($ioObject->dirsep(), '/', $destFile);
    }
	
    /**
     * Retrive media config
     *
     * @return Mage_Catalog_Model_Product_Media_Config
     */
    protected function _getMadiaConfig()
    {
        return Mage::getSingleton('catalog/product_media_config');
    }	 
      	
    protected function getMemoryLimit()
    {
        $size_str = ini_get('memory_limit');
        switch (substr ($size_str, -1))
        {
            case 'M': case 'm': return (int)$size_str * 1048576;
            case 'K': case 'k': return (int)$size_str * 1024;
            case 'G': case 'g': return (int)$size_str * 1073741824;
            default: return $size_str;
        }
    }  

	
	  public function sortOptions($o1, $o2)
	  {
	    $a = (int) $o1->getSortOrder();
	    $b = (int) $o2->getSortOrder();	
      if ($a == $b)
          return 0;
      return ($a < $b) ? -1 : 1;
	  } 


	  public function _addOptionTemplatesToProduct($product)
	  {
      $options = Mage::getResourceModel('optionextended/template')->getTemplateOptionsCollection($product->getId(),(int) $product->getStoreId());

      $coreOptions = Mage::getResourceModel('catalog/product_option_collection')->addFieldToFilter('product_id', $product->getId());

      foreach($coreOptions as $option)
        $options[] = $option;
        
      usort($options, array($this, "sortOptions"));
      
      $hasRequired = false;
      foreach ($options as $option){
        $option->setProduct($product); 
        $product->addOption($option);
        if (!$hasRequired && $option->getIsRequire() == 1)
          $hasRequired = true;
      }
      
      if (count($options) > 0){
        $product->setHasOptions(true);
        $product->setRequiredOptions($hasRequired);
      }  
      
	  }	
	  
	  
// added to bypass the option type check by option id
// magento 1.6.1 /app/code/core/Mage/Sales/controllers/DownloadController.php
// function downloadCustomOptionAction
//
// optiontemplate options has temporary option ids
// so it is not possible retrive an option by temporary id
//
// the following two functions create a fake option with type File
// when any script tries to load an option by not existen id
    public function optionLoadBefore(Varien_Event_Observer $observer)
    {

      $object = $observer->getEvent()->getObject();      
      $resource_name = $object->getResourceName();
      
      if ($resource_name == 'catalog/product_option'){

        $this->_option_id = $observer->getEvent()->getValue();
        
      }
      
    }
    
    
    public function optionLoadAfter(Varien_Event_Observer $observer)
    {
 
      $object = $observer->getEvent()->getObject();
      $resource_name = $object->getResourceName();
      
      if ($resource_name == 'catalog/product_option'){
      
        if(!$object->getId() && $this->_option_id && $this->_product_id){
          $object->setId($this->_option_id);
          $object->setProductId($this->_product_id);
          $object->setType("file");
        }

      } else if ($resource_name == 'sales/quote_item_option'){
      
        $this->_product_id = $object->getProductId(); 
        
      }
      
    }
	
}

