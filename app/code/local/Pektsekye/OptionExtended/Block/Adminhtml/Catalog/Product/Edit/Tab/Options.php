<?php


class Pektsekye_OptionExtended_Block_Adminhtml_Catalog_Product_Edit_Tab_Options extends Mage_Adminhtml_Block_Widget
{
    protected $_productInstance;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('optionextended/catalog/product/edit/options.phtml');
    }


    protected function _prepareLayout()
    {

        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Add New Option'),
                    'class' => 'add',
                    'id'    => 'add_new_defined_option',
                    'onclick'   => 'optionExtended.add()'
                ))
        );  
              
        $this->setChild('option_template',
            $this->getLayout()->createBlock('optionextended/adminhtml_catalog_product_edit_tab_options_template')
                ->setProductId($this->getProduct()->getId())
           );
                
        return parent::_prepareLayout();
    }


    public function getProduct()
    {
        if (!$this->_productInstance) {
            if ($product = Mage::registry('product')) {
                $this->_productInstance = $product;
            } else {
                $this->_productInstance = Mage::getSingleton('catalog/product');
            }
        }

        return $this->_productInstance;
    }
    
    
    public function getOptions()
    { 	
			$product = $this->getProduct();			
			$product_id = $product->getId();
			$storeId = $product->getStoreId();			
			$options = Mage::getModel('optionextended/option')
			->getCollection()                 
	    ->joinTitles($storeId)		 
      ->joinCoreOptionData()							
			->addFieldToFilter('core_option_table.product_id', $product_id)
      ->setOrder('sort_order', 'asc')
      ->setOrder('title', 'asc');	
      
      return $options;      				
		}


    public function getOptionsHtml()
    {
      $storeId = $this->getProduct()->getStoreId();
      $options = $this->getOptions() 
		    ->joinNotes($storeId)                   		
        ->joinPrices($storeId);   
          
      $html = '';        
      foreach ($options as $option){
        $option->setStoreId($storeId);
        $html .= $this->getLayout()->createBlock('optionextended/adminhtml_catalog_product_edit_tab_options_option', '', array('option'=>$option))->toHtml();
      }
      return $html;                
    }
    
    
    public function getOptionsJsonData()
    { 
    
      $scope = (int) Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);	    
			$config = array(); 
      
			$mediaconfig = Mage::getSingleton('catalog/product_media_config');	
			$product = $this->getProduct();			
			$product_id = $product->getId();
			$storeId = $product->getStoreId();			
			$options = Mage::getModel('optionextended/option')
			->getCollection()
			->joinTitles($storeId)											
      ->joinCoreOptionData()							
			->addFieldToFilter('main_table.product_id', $product_id)
      ->setOrder('sort_order', 'asc')
      ->setOrder('title', 'asc');	
      	
      
			foreach ($options as $option){
				$id = (int) $option->getOptionId();
						
				$config['optionTypes'][$id]  = $option->getType();
				$config['optionTitles'][$id] = $option->getTitle();											
												
				$config['optionIds'][] = $id;
		  	$config['optionIsNotVisible'][$id] = true;
		  	$config['optionIsNotLoaded'][$id] = true;			  
		  	
				if (!is_null($option->getRowId())){
				  $rowId = (int) $option->getRowId();				
			  	$config['rowIds'][] = $rowId;
		  	  $config['rowIdIsset'][$rowId] = 1;			  					  		
		  	  $config['rowIdByOption'][$id] = $rowId;
          $config['optionByRowId'][$rowId] = $id; 		  	  
		  	}			  																
			}
			
			if (isset($config['optionIds'])){
			  $t = $config['optionIds'];
			  sort($t);
			  $config['lastOptionId'] = end($t);			
      }

			
			$values = Mage::getModel('optionextended/value')
				->getCollection()
			  ->joinTitles($storeId)
        ->joinPrices($storeId)
				->joinDescriptions($storeId)        				  							
        ->joinCoreOptionValueData()						
				->addFieldToFilter('product_id', $product_id)
        ->setOrder('sort_order', 'asc')
        ->setOrder('title', 'asc');
        				
      $selectIds = array(); 
			$helper = Mage::helper('catalog/image');             
			foreach ($values as $value) {
				$id = (int) $value->getOptionTypeId();
																
				$optionId = (int) $value->getOptionId();
				$rowId = (int) $value->getRowId();	
        $children = array();
        if ($value->getChildren() != ''){
          $children = explode(',', $value->getChildren());
          foreach ($children as $k => $v){
            $children[$k] = (int) $v;
            $config['parentRowIdsOfRowId'][$v][] = $rowId;
          }  
        }	
        
				$config['valueTitles'][$rowId] = $value->getTitle();
								        								
				$selectIds[] = $id;								
		  	$config['selectIdByRowId'][$rowId] = $id;						
		  	$config['rowIds'][] = $rowId;
		  	$config['rowIdIsset'][$rowId] = 1;			  			  	
		  	$config['rowIdsByOption'][$optionId][] = $rowId;
		  	$config['rowIdsByOptionIsset'][$optionId][$rowId] = 1;			  			
		  	$config['rowIdBySelectId'][$id] = $rowId;
        $config['childrenByRowId'][$rowId] = $children;
        $config['optionByRowId'][$rowId] = $optionId;        	  			  	  			  												
			}	
			
			if (isset($config['rowIds'])){
			  $t = $config['rowIds'];
			  sort($t);
			  $config['lastRowId'] = end($t);			
      }

			if (count($selectIds) > 0){
			  sort($selectIds);
			  $config['lastSelectId'] = end($selectIds);			
      }
     
          
      return Zend_Json::encode($config);
    }
    
    


    public function getOptionTemplateHtml()
    {
        return $this->getChildHtml('option_template');
    }
    
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
    
    public function getPriceValue($value, $type)
    {
        if ($type == 'percent') {
            return number_format($value, 2, null, '');
        } elseif ($type == 'fixed') {
            return number_format($value, 2, null, '');
        }
    }  
    
}
