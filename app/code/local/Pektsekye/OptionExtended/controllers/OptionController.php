<?php

class Pektsekye_OptionExtended_OptionController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('optionextended/adminhtml_catalog_product_edit_tab_options_option')->toHtml()
        );
    }

    public function templateAction()
    {
      $templateId = (int) $this->getRequest()->getParam('template_id'); 
      $productId = (int) $this->getRequest()->getParam('product_id');               
            
			$helper = Mage::helper('catalog/image');			
			$product = Mage::getModel('catalog/product')->load($productId); 			     
      $options = Mage::getResourceModel('optionextended/template')->getTemplateData($templateId, 0);


		  $typeIndexes = array(						
			  'field'		  => 1,	
			  'area' 		  => 2,	
			  'file' 		  => 3,	
			  'drop_down' => 4,	
			  'radio' 		=> 5,	
			  'checkbox'  => 6,	
			  'multiple'  => 7,				
			  'date' 		  => 8,					
			  'date_time' => 9,	
			  'time'			=> 10											
		  );

      $layoutIndexes = array(
        'radio' => array(
            'above'        =>0,        
            'before'       =>1,
            'below'        =>2,
            'swap'         =>3,
            'grid'         =>4,  
            'gridcompact'  =>5,              
            'list'         =>6               
          ),        
        'checkbox' => array(
            'above'       =>0,         
            'below'       =>1,
            'grid'        =>2,   
            'gridcompact' =>3,              
            'list'        =>4    
          ),        
        'drop_down' => array(
            'above'     =>0,         
            'before'    =>1,
            'below'     =>2,
            'swap'      =>3,
            'picker'    =>4, 
            'pickerswap'=>5                 
          ),
        'multiple' => array(
            'above'=>0,        
            'below'=>1         
          )           
      );

      $oi = 0;            
      $js = array();
			foreach ($options as $k => $option){
		
        $js[$templateId][$oi] = array(       
          'title'         => (string) $option->getTitle(),
          'type'          => (string) $option->getType(),       
          'typeIndex'     => (int) $typeIndexes[$option->getType()],                   
          'isRequireIndex'=> $option->getIsRequire() == 1 ? 0 : 1,         
          'sortOrder'     => (int) $option->getSortOrder(),
          'price'         => number_format($option->getPrice(), 2, null, ''),
          'priceType'     => (string) $option->getPriceType(),
          'priceTypeIndex'=> $option->getPriceType() == 'percent' ? 1 : 0,          
          'sku'           => (string) $option->getSku(),
          'maxCharacters' => (string) $option->getMaxCharacters(),
          'fileExtension' => (string) $option->getFileExtension(),
          'imageSizeX'    => (int) $option->getImageSizeX(),
          'imageSizeY'    => (int) $option->getImageSizeY(),
          'note'          => (string) $option->getNote(),     
          'layoutIndex'   => isset($layoutIndexes[$option->getType()]) ? (int) $layoutIndexes[$option->getType()][$option->getLayout()] : 0,         
          'popupChecked'  => $option->getPopup() == 1,  
          'popupDisabled' => $option->getLayout() == 'swap'                                                                          
        );

        if (!is_null($option->getRowId()))
          $js[$templateId][$oi]['rowId'] = (int) $option->getRowId();  
		
        if (!is_null($option->getValues())){
        
          $vi = 0;        
			    foreach ($option->getValues() as $kk => $value) {
			    
            $imageObj = array();
		        if ($value->getImage() != ''){
		          $imageObj['url'] = $helper->init($product, 'thumbnail', $value->getImage())->resize(40)->__toString();
		          $imageObj['savedas'] = $value->getImage();
		          $imageObj['toduplicate'] = 1;
            }
                       
            $sdChecked = false;
            if ($option->getSelectedByDefault() != '')
              $sdChecked = in_array($value->getRowId(), explode(',', $option->getSelectedByDefault()));

            $children = array();
		        if ($value->getChildren() != ''){            
              $children = explode(',', $value->getChildren());
              foreach ($children as $k => $v)
                $children[$k] = (int) $v;               
            }
            
            $js[$templateId][$oi]['values'][$vi] = array(
              'rowId'           => (int) $value->getRowId(),            		           
              'title'           => (string) $value->getTitle(),			
              'price'           => number_format($value->getPrice(), 2, null, ''),									
              'priceTypeIndex'  => $value->getPriceType() == 'percent' ? 1 : 0, 
              'sku'             => (string) $value->getSku(),
              'sortOrder'       => (string) $value->getSortOrder(),			         
              'imageObject'     => $imageObj,                   			  																		
              'description'     => (string) $value->getDescription(),
              'sdIsChecked'     => $sdChecked,
              'children'        => $children      			  		
            );
            $vi++;
			    }
			    
        }
        $oi++; 
		  }
        $this->getResponse()->setBody(Zend_Json::encode($js));
    }
    
    
    protected function _isAllowed()
    {
        return true;
    }
    
    
}
