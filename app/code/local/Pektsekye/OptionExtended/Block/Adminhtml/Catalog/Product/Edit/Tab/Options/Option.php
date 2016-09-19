<?php


class Pektsekye_OptionExtended_Block_Adminhtml_Catalog_Product_Edit_Tab_Options_Option extends Mage_Adminhtml_Block_Widget
{   
    protected $_values;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('optionextended/catalog/product/edit/options/option.phtml');                 
    }
    
    protected function initOption()
    {
        if (is_null($this->getOption())){
          $optionId = $this->getRequest()->getParam('option_id');
          $productId = (int) $this->getRequest()->getParam('product_id');          
          $storeId = (int) $this->getRequest()->getParam('store');                 
          $option = Mage::getModel('optionextended/option')
            ->getCollection()
            ->addFieldToFilter('main_table.option_id', $optionId) 
			      ->joinNotes($storeId)                   
			      ->joinTitles($storeId)		
            ->joinPrices($storeId)	 
            ->joinCoreOptionData()	                   		    			    		        
            ->getFirstItem();
          $this->setOption($option);
          $this->setStoreId($storeId);
          $this->setProductId($productId);                   
        } else {
          $this->setStoreId($this->getOption()->getStoreId());
        }

        $this->getOption()->setId($this->getOption()->getOptionId());
        
        $this->setGroup(Mage::getModel('catalog/product_option')->getGroupByType($this->getOption()->getType()));
        
        if ($this->getStoreId() != 0) { 
                    
          $this->getOption()->setCheckboxScopeTitle($this->getCheckboxScopeHtml($this->getOption()->getId(), 'title', is_null($this->getOption()->getStoreTitle())));
          $this->getOption()->setTitleDisabled(is_null($this->getOption()->getStoreTitle()) ? 'disabled="disabled"' : '');
           
          if ($this->getGroup() != 'select' && Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE == (int) Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE)) {
            $this->getOption()->setCheckboxScopePrice($this->getCheckboxScopeHtml($this->getOption()->getId(), 'price', is_null($this->getOption()->getStorePrice())));
            $this->getOption()->setPriceDisabled(is_null($this->getOption()->getStorePrice()) ? 'disabled="disabled"' : '');
          } 
                
          $this->getOption()->setCheckboxScopeNote($this->getCheckboxScopeHtml($this->getOption()->getId(), 'note', is_null($this->getOption()->getStoreNote())));
          if (is_null($this->getOption()->getStoreNote())){          
            $this->getOption()->setNoteDisabled('disabled="disabled"');
            $this->getOption()->setNoteShowHidden('style="display:none"');         
          }           
        }    
        
        if ($this->getGroup() == 'select'){         
          $this->getOption()->setPopupChecked($this->getOption()->getPopup() == 1 ? 'checked="checked"' : '');
          $this->getOption()->setPopupDisabled(in_array($this->getOption()->getLayout(), array('swap')) ? 'disabled="disabled"' : '');
        } else {
          $this->getOption()->setPriceValue($this->getPriceValue($this->getOption()->getPrice(), $this->getOption()->getPriceType()));
        }
        
        if (Mage::getStoreConfig('admin/optionextended/selected_by_default') == 1)        
         $this->getOption()->setSdIds(explode(',', $this->getOption()->getSelectedByDefault()));
      
               
        $this->assign('group', $this->getGroup());                
        $this->assign('id', $this->getOption()->getId()); 
        $this->assign('option', $this->getOption());

    }


    protected function _prepareLayout()
    {
        $this->initOption();
    
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete Option'),
                    'class' => 'delete delete-product-option',
                    'onclick' => 'optionExtended.deleteOption('.$this->getOption()->getId().')'
                ))
        );
        
        $this->setChild('delete_select_row_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete Row'),
                    'class' => 'delete delete-select-row icon-btn'                    
                ))
        );
                                           
			  $this->setChild('duplicate_option_button',
              $this->getLayout()->createBlock('adminhtml/widget_button')
                  ->setData(array(
                      'label'     => $this->__('Duplicate Option'),
                      'onclick'   => 'optionExtended.duplicate('.$this->getOption()->getId().')',
                      'class' => 'add optionextended-duplicate-option-button'
                  )));
                  
        $this->setChild('add_select_row_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Add New Row'),
                    'class' => 'add add-select-row',
                    'id'    => 'add_select_row_button_' . $this->getOption()->getId(),
                    'onclick' => 'optionExtended.addRow('.$this->getOption()->getId().')'                    
                ))
        );
       
        
        return parent::_prepareLayout();
    }


    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }
    
    public function getDeleteRowButtonHtml($value)
    {
        return $this->getChild('delete_select_row_button')
          ->setData('onclick', 'optionExtended.deleteRow('.$this->getOption()->getId().','.$value->getId().')')
          ->toHtml();
    }	
    
    public function getDuplicateOptionButtonHtml()
    {
        return $this->getChildHtml('duplicate_option_button');
    }

    public function getAddRowButtonHtml()
    {
        return $this->getChildHtml('add_select_row_button');
    }
   
    
    public function getShowTextArea($value = null)
    {
      if ($this->getIsWysiwygEnabled()){
        $wysiwygUrl = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/wysiwyg');
        if (!is_null($value))
          $fieldId = 'optionextended_'.$value->getId().'_description';
        else  
          $fieldId = 'optionextended_'.$this->getOption()->getId().'_note';      
        $extra = 'onclick="catalogWysiwygEditor.open(\''. $wysiwygUrl .'\', \''. $fieldId .'\')"';   
      } else {
        $extra = 'onclick="optionExtended.showTextArea(this)"';     
      } 

     return $extra;
    }


    public function getClickToEditText()
    {
        return $this->getIsWysiwygEnabled() ? Mage::helper('catalog')->__('WYSIWYG Editor') : $this->__('Click to edit');   
    }    


    public function getIsWysiwygEnabled()
    {
        $version = Mage::getVersion();
        return version_compare($version, '1.4.0.0') >= 0 && Mage::getSingleton('cms/wysiwyg_config')->isEnabled();
    }  
	    
	    
    public function  getSdColumnInput()   
    {        	
      if ($this->getOption()->getType() == 'drop_down' || $this->getOption()->getType() == 'radio')	
		    $input = '<input type="radio" name="optionextended_'.$this->getOption()->getId().'_sd" id="optionextended_'.$this->getOption()->getId().'_sd" onclick="optionExtended.uncheckAllRadio('.$this->getOption()->getId().')" class="radio" value="" '. ($this->getOption()->getSelectedByDefault() == '' ? 'checked="checked"' : '') .' />';
	    else 
		    $input = '<input type="checkbox" name="optionextended_'.$this->getOption()->getId().'_sd" id="optionextended_'.$this->getOption()->getId().'_sd" onclick="optionExtended.checkAllCheckboxes(this,'.$this->getOption()->getId().')" class="checkbox" value="" title="'.$this->__('Mark all rows as Selected By Default').'"/>';			
      return $input;			  
	  }
	  	    
    public function  getSdCellInput($valueObject)   
    {    
      $checked = in_array($valueObject->getRowId(), $this->getOption()->getSdIds()) ? 'checked="checked"' : '';    
      if ($this->getOption()->getType() == 'drop_down' || $this->getOption()->getType() == 'radio')	
        $input = '<input onclick="optionExtended.onRadioCheck('.$this->getOption()->getId().','.$valueObject->getId().')" type="radio" class="radio optionextended-sd-input" name="optionextended_'.$this->getOption()->getId().'_sd" id="optionextended_value_'.$valueObject->getId().'_sd" title="'.$this->__('Mark row as Selected By Default').'" value="" '. $checked .'/>';                      
      else
        $input = '<input onclick="optionExtended.onCheckboxCheck(this,'.$this->getOption()->getId().','.$valueObject->getId().')" type="checkbox" class="checkbox optionextended-sd-input" name="optionextended_'.$this->getOption()->getId().'_sd[]" id="optionextended_value_'.$valueObject->getId().'_sd" title="'.$this->__('Mark row as Selected By Default').'" value="" '. $checked .'/>';              
      return $input;			  
	  }
	    
	    
    public function  getTypeSelect()   
    {
        $options = Mage::getSingleton('adminhtml/system_config_source_product_options_type')->toOptionArray(); 
        
        $groupIndexes = array(
            'text' => 1,
            'file' => 2,
            'select' => 3,
            'date' => 4                                                                                                                                                            
          );
          
        $groupIndex = $groupIndexes[$this->getGroup()];        
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => 'product_option_'.$this->getOption()->getId().'_type',
                'class' => 'select select-product-option-type required-option-select',
                'extra_params' => 'onchange="this.setHasChanges();'. ($this->getGroup() == 'select' ? 'optionExtended.reloadLayoutSelect('.$this->getOption()->getId().',this)' : '') .';"'                
            ))
            ->setName('product[options]['.$this->getOption()->getId().'][type]')
            ->setValue($this->getOption()->getType())            
            ->setOptions($options[$groupIndex]['value']);

        return $select->getHtml();

    }


    public function getRequireSelect()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => 'product_option_'.$this->getOption()->getId().'_is_require',
                'class' => 'select', 
                'extra_params' => 'onchange="this.setHasChanges()"'                              
            ))
            ->setName('product[options]['.$this->getOption()->getId().'][is_require]')
            ->setValue($this->getOption()->getIsRequire())             
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $select->getHtml();
    }
 
 
     public function getLayoutSelect()
    {
  
      switch($this->getOption()->getType()){
        case 'radio' :
          $options = array(
            array('value' =>'above'      , 'label'=>$this->__('Above Option')),        
            array('value' =>'before'     , 'label'=>$this->__('Before Option')),
            array('value' =>'below'      , 'label'=>$this->__('Below Option')),
            array('value' =>'swap'       , 'label'=>$this->__('Main Image')),
            array('value' =>'grid'       , 'label'=>$this->__('Grid')),    
            array('value' =>'gridcompact', 'label'=>$this->__('Grid Compact')),                
            array('value' =>'list'       , 'label'=>$this->__('List'))                
          );
        break;
        case 'checkbox' :
          $options = array(
            array('value' =>'above'      , 'label'=>$this->__('Above Option')),         
            array('value' =>'below'      , 'label'=>$this->__('Below Option')),
            array('value' =>'grid'       , 'label'=>$this->__('Grid')),
            array('value' =>'gridcompact', 'label'=>$this->__('Grid Compact')),                    
            array('value' =>'list'       , 'label'=>$this->__('List'))     
          );
        break;
        case 'drop_down' :
          $options = array(
            array('value' =>'above'     , 'label'=>$this->__('Above Option')),         
            array('value' =>'before'    , 'label'=>$this->__('Before Option')),
            array('value' =>'below'     , 'label'=>$this->__('Below Option')),
            array('value' =>'swap'      , 'label'=>$this->__('Main Image')),
            array('value' =>'picker'    , 'label'=>$this->__('Color Picker')), 
            array('value' =>'pickerswap', 'label'=>$this->__('Picker & Main'))                   
          );
        break;
        case 'multiple' :
          $options = array(
            array('value' =>'above', 'label'=>$this->__('Above Option')),         
            array('value' =>'below', 'label'=>$this->__('Below Option'))          
          );
        break;            
      }
      
      $select = $this->getLayout()->createBlock('adminhtml/html_select')
          ->setData(array(
              'id' => 'optionextended_'.$this->getOption()->getId().'_layout',
              'class' => 'select optionextended-layout-select',
              'extra_params' => 'onchange="optionExtended.changePopup('.$this->getOption()->getId().');this.setHasChanges()"'                               
          ))
          ->setName('product[options]['.$this->getOption()->getId().'][layout]')
          ->setValue($this->getOption()->getLayout())             
          ->setOptions($options);

      return $select->getHtml();
    }

    public function getPriceTypeSelectHtml($valueObject = null)
    {    
      if ($valueObject != null){         
        $id = 'product_option_value_'.$valueObject->getId().'_price_type';
        $name = 'product[options]['.$this->getOption()->getId().'][values]['.$valueObject->getId().'][price_type]';
      } else { 
        $id = 'product_option_'.$this->getOption()->getId().'_price_type';
        $name = 'product[options]['.$this->getOption()->getId().'][price_type]';      
      }
                     
      $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $id,
                'class' => 'select product-option-price-type',
                'extra_params' => 'onchange="this.setHasChanges()"'                          
            ))           
            ->setName($name)
            ->setValue($valueObject != null ? $valueObject->getPriceType() : $this->getOption()->getPriceType())             
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_product_options_price')->toOptionArray());
     
      return $select->getHtml();                
    }


    public function _getValues()
    {
      if (!isset($this->_values)){
        $this->_values = Mage::getModel('optionextended/value')
	        ->getCollection()
	        ->joinDescriptions($this->getStoreId())
          ->joinTitles($this->getStoreId())		
          ->joinPrices($this->getStoreId())	 
          ->joinCoreOptionValueData()		
          ->addFieldToFilter('option_id', $this->getOption()->getId())
          ->setOrder('sort_order', 'asc')
          ->setOrder('title', 'asc');
      }    
         
      return $this->_values;    
    }
    
        
    public function getValues()
    {

      $scope = (int) Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);		      							
			$helper = Mage::helper('catalog/image');			
			$product = !is_null(Mage::registry('product')) ? Mage::registry('product') : Mage::getModel('catalog/product')->load($this->getProductId());				
			
      $values = $this->_getValues();
			foreach ($values as $value) {
				$value->setId($value->getOptionTypeId());

        $imageJs = array();	
				if ($value->getImage() != ''){
				  $thumbnail = $helper->init($product, 'thumbnail', $value->getImage())->resize(40)->__toString();
				  $imageJs['url'] = $thumbnail;
				  $imageJs['savedas'] = $value->getImage();
				  $value->setRollOver($this->__('Roll Over for Preview'));
			    $value->setImageJson(Zend_Json::encode($imageJs));				   			  
				}	else {
				  $value->setRollOver($this->__('Roll Over for Uploader'));
				}
			  $value->setImageObj($imageJs);				  

			  				
        if ($this->getStoreId() != 0) {      
          $value->setCheckboxScopeTitle($this->getCheckboxScopeHtml($this->getOption()->getId(), 'title', is_null($value->getStoreTitle()), $value->getId()));
          $value->setTitleDisabled(is_null($value->getStoreTitle()) ? 'disabled="disabled"' : '');
          
          if ($scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE) {
            $value->setCheckboxScopePrice($this->getCheckboxScopeHtml($this->getOption()->getId(), 'price', is_null($value->getStorePrice()), $value->getId()));
            $value->setPriceDisabled(is_null($value->getStorePrice()) ? 'disabled="disabled"' : '');
          } 
                
          $value->setCheckboxScopeDescription($this->getCheckboxScopeHtml($this->getOption()->getId(), 'description', is_null($value->getStoreDescription()), $value->getId()));
          $value->setDescriptionDisabled(is_null($value->getStoreDescription()) ? 'disabled="disabled"' : '');
          if (is_null($value->getStoreDescription())){          
            $value->setDescriptionDisabled('disabled="disabled"');
            $value->setDescriptionShowHidden('style="display:none"');         
          }                     
        } 	
        
        $value->setPriceFormated($this->getPriceValue($value->getPrice(), $value->getPriceType()));
        			
      }
    
      return  $values;  
    }
    
    
    public function getRowsData()
    {
      $js = array();
      $values = $this->_getValues();      
			foreach ($values as $value) {
        $id = (int) $value->getOptionTypeId();
        $rowId = (int) $value->getRowId();
             							
        $js[$rowId] = array(
          'id'               => (int) $this->getOption()->getId(),		
          'select_id'        => $id,	            
          'row_id'           => $rowId,
          'type'             => $this->getOption()->getType(),
          'title_esc'        => $this->htmlEscape($value->getTitle()),			
          'price'            => $value->getPriceFormated(),									
          'price_type_select'=> $this->getPriceTypeSelectHtml($value),
          'sku_esc'          => $this->htmlEscape($value->getSku()),
          'sort_order'       => $value->getSortOrder(),			         
          'image_object'     => $value->getImageObj(),
          'image_json'       => $this->htmlEscape($value->getImageJson()),
          'roll_over'        => $value->getRollOver(),                    			  																		
          'description_esc'  => $this->htmlEscape($value->getDescription()),
          'sd_cell_input'    => $this->getSdCellInput($value),
          
          'title'            => $value->getTitle(),
          'priceTypeIndex'   => $value->getPriceType() == 'percent' ? 1 : 0, 
          'sku'              => $value->getSku(),         
          'description'      => $value->getDescription(),                                       
          'sdIsChecked'      => in_array($value->getRowId(), $this->getOption()->getSdIds())          			  		
        );

        if ($this->getStoreId() != 0) {
          $js[$rowId]['store_title_is_null'] = is_null($value->getStoreTitle());	        		      		
          $js[$rowId]['store_description_is_null'] = is_null($value->getStoreDescription());        
          if (Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE == (int) Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE))
            $js[$rowId]['store_price_is_null'] = is_null($value->getStorePrice());           				  					
        }	      
     } 
			
        return Zend_Json::encode($js);
    }
    
    
    public function getCheckboxScopeHtml($id, $name, $checked=true, $select_id = -1)
    {
      if ($select_id == -1) {
        if ($name == 'note'){
          $inputId   = "optionextended_{$id}_note";        
          $inputName = "product[options][{$id}][scope][optionextended_note]";          
        } else {
          $inputId   = "product_option_{$id}_{$name}";        
          $inputName = "product[options][{$id}][scope][{$name}]";
        }
      } else {
        if ($name == 'description'){
          $inputId   = "optionextended_{$select_id}_description";        
          $inputName = "product[options][{$id}][values][{$select_id}][scope][optionextended_description]";          
        } else {
          $inputId   = "product_option_value_{$select_id}_{$name}";        
          $inputName = "product[options][{$id}][values][{$select_id}][scope][{$name}]";
        }      
      }
      
      $checkbox = '<br/><input type="checkbox" id="'. $inputId .'_use_default" class="product-option-scope-checkbox" name="'. $inputName .'" value="1" '. ($checked ? 'checked="checked"' : '') .'  onclick="optionExtended.setScope(this, \''. $inputId .'\', \''. $name .'\')"/>'.
                  '<label class="normal" for="'. $inputId .'_use_default"> '. Mage::helper('adminhtml')->__('Use Default Value') .'</label>';
                    
        return $checkbox;
    }



    public function getPriceValue($value, $type)
    {
        if ($type == 'percent') {
            return number_format($value, 2, null, '');
        } elseif ($type == 'fixed') {
            return number_format($value, 2, null, '');
        }
    }
    
    
    public function getValuesTableWidth()
    {
        $width = 1280;
		    if (Mage::getStoreConfig('admin/optionextended/dependency') == 0)
          $width =  $width - 242; 		
		    if (Mage::getStoreConfig('admin/optionextended/images') == 0)
          $width =  $width - 155; 			    
	    	if (Mage::getStoreConfig('admin/optionextended/description') == 0)
          $width =  $width - 241;	  	        	      	      	        
		    if (Mage::getStoreConfig('admin/optionextended/selected_by_default') == 0)
          $width =  $width - 25;
        
        return $this->getGroup() == 'select' ? 'style="width:'.$width.'px"' : '';
    }    
    
    
}
