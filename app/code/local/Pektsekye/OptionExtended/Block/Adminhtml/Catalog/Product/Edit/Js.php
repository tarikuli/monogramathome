<?php
class Pektsekye_OptionExtended_Block_Adminhtml_Catalog_Product_Edit_Js extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Option
{ 

	  protected function _prepareLayout()
    {				 
      $this->setChild('delete_button',
          $this->getLayout()->createBlock('adminhtml/widget_button')
              ->setData(array(
                  'label' => Mage::helper('catalog')->__('Delete Option'),
                  'class' => 'delete delete-product-option',
                  'onclick' => 'optionExtended.deleteOption({{id}})'
              ))
      );
      
      
      $this->setChild('delete_select_row_button',
          $this->getLayout()->createBlock('adminhtml/widget_button')
              ->setData(array(
                  'label' => Mage::helper('catalog')->__('Delete Row'),
                  'class' => 'delete delete-select-row icon-btn',
                  'onclick'   => 'optionExtended.deleteRow({{id}},{{select_id}})'                     
              ))
      );
                                         
      $this->setChild('duplicate_option_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => $this->__('Duplicate Option'),
                    'onclick'   => 'optionExtended.duplicate({{id}})',
                    'class' => 'add optionextended-duplicate-option-button'
                )));
                
      $this->setChild('add_select_row_button',
          $this->getLayout()->createBlock('adminhtml/widget_button')
              ->setData(array(
                  'label' => Mage::helper('catalog')->__('Add New Row'),
                  'class' => 'add add-select-row',
                  'id'    => 'add_select_row_button_{{id}}',
                  'onclick'   => 'optionExtended.addRow({{id}})'                  
              ))
      );

    }

    
    
    public function getPriceTypeSelectHtml($type = 'option')
    {    
      if ($type == 'value'){         
        $id = 'product_option_value_{{select_id}}_price_type';
        $name = 'product[options][{{id}}][values][{{select_id}}][price_type]';
      } else { 
        $id = 'product_option_{{id}}_price_type';
        $name = 'product[options][{{id}}][price_type]';   
      }
                     
      $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $id,
                'class' => 'select product-option-price-type',
                'extra_params' => 'onchange="this.setHasChanges()"'                          
            ))           
            ->setName($name)
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_product_options_price')->toOptionArray());
     
      return $select->getHtml();                
    }	
	

    public function getShowTextArea($type = 'option')
    {
      if ($this->getIsWysiwygEnabled()){
        $wysiwygUrl = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/wysiwyg');
        if ($type == 'value')
          $fieldId = 'optionextended_{{select_id}}_description';
        else  
          $fieldId = 'optionextended_{{id}}_note';      
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

    
    public function getAccordionOptionUrl()
    {
        return $this->getUrl('adminhtml/option/index');
    }
    
    public function getTemplateDataUrl()
    {
        return $this->getUrl('adminhtml/option/template');
    }
        
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

        
    public function getDeleteRowButtonHtml()
    {
        return $this->getChildHtml('delete_select_row_button');
    }	
    
    public function getDuplicateOptionButtonHtml()
    {
        return $this->getChildHtml('duplicate_option_button');
    }

    public function getAddRowButtonHtml()
    {
        return $this->getChildHtml('add_select_row_button');
    }

    public function getConfig()
    {
        if(is_null($this->_config)) {
            $this->_config = new Varien_Object();
        }

        return $this->_config;
    }

    public function getConfigJson()
    {
		$this->getConfig()->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/catalog_product_gallery/upload'));		
        $this->getConfig()->setParams(array('form_key' => $this->getFormKey()));

		$this->getConfig()->setFileField('image');
        $this->getConfig()->setFilters(array(
            'images'    => array(
                'label' => Mage::helper('adminhtml')->__('Images (.gif, .jpg, .png)'),
                'files' => array('*.gif','*.jpg','*.jpeg','*.png')
            )
        ));
        $this->getConfig()->setReplaceBrowseWithRemove(true);
        $this->getConfig()->setWidth('32');
        $this->getConfig()->setHideUploadButton(true);
        return Zend_Json::encode($this->getConfig()->getData());
    }

		
    public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => 'product_option_{{id}}_type',
                'class' => 'select select-product-option-type required-entry',
                'extra_params' => 'onchange="optionExtended.loadStepTwo({{id}});this.setHasChanges()"'
            ))
            ->setName('product[options][{{id}}][type]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_product_options_type')->toOptionArray());

        return $select->getHtml();
    }     
    
        
    
    public function getStoreId()
    {    
        return $this->getProduct()->getStoreId();  
    }  
    
    public function getIsContinueEdit()
    {
      return !is_null($this->getRequest()->getParam('back')) && $this->getRequest()->getParam('tab') == 'product_info_tabs_customer_options' ? 'true' : 'false';
    }    
    
    public function getOpenedOptionIds()
    {  
        $hiddenOptionIds = array();
        $ids = $this->getRequest()->getParam('hidden_ids');      
        if (!is_null($ids)){
				  $hiddenOptionIds = explode('_', $ids);
				  foreach ($hiddenOptionIds as $k => $v)
				   $hiddenOptionIds[$k] = (int) $v;
				}  				   
        return Zend_Json::encode($hiddenOptionIds);
    }        
    
    public function getIsExpanded()
    {
        return (int) $this->getRequest()->getParam('is_expanded');
    }

    public function getOptionsUrl()
    {
        return $this->getUrl('*/optiontemplate_option/index', array('template_id' => '{{templateId}}'));                          
    }
    
}
