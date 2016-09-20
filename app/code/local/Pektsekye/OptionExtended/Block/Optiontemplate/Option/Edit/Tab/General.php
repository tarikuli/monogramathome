<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Option_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{

  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();

      $fieldset = $form->addFieldset('optionextended_form', array('legend'=>Mage::helper('adminhtml')->__('General Information')));
      
      $disabled = false;
      $useDefaultHtml = '';
      if (!is_null(Mage::registry('current_option')->getId()) && Mage::registry('current_option')->getStoreId() != 0){
        $checked = '';
        if (is_null(Mage::registry('current_option')->getStoreTitle())){
          $checked = 'checked="checked"';
          $disabled = true;          
        } 
       $useDefaultHtml = '<input type="checkbox" id="title_use_default" class="checkbox" name="title_use_default" onclick="toggleValueElements(this, this.parentNode)" value="1" '.$checked.'/>&nbsp;'.
                         '<label class="normal" for="title_use_default">'.Mage::helper('adminhtml')->__('Use Default').'</label>';  
      }     
      
      $fieldset->addField('title', 'text', array(
          'name'      => 'title',    
          'label'     => Mage::helper('catalog')->__('Title'),
          'disabled'  => $disabled,              
          'required'  => true,
          'after_element_html' => $useDefaultHtml 
      ));


      $fieldset->addField('type', 'select', array(
          'name'      => 'type',
          'label'     => Mage::helper('core')->__('Type'),
          'options'   => array(
                          "" => Mage::helper('adminhtml')->__('-- Please Select --'),          
                          "field" => Mage::helper('adminhtml')->__('Field'),
                          "area" => Mage::helper('adminhtml')->__('Area'),            
                          "file" => Mage::helper('adminhtml')->__('File'),            
                          "drop_down" => Mage::helper('adminhtml')->__('Drop-down'),
                          "radio" => Mage::helper('adminhtml')->__('Radio Buttons'),
                          "checkbox" => Mage::helper('adminhtml')->__('Checkbox'),
                          "multiple" => Mage::helper('adminhtml')->__('Multiple Select'),
                          "date" => Mage::helper('adminhtml')->__('Date'),
                          "date_time" => Mage::helper('adminhtml')->__('Date & Time'),
                          "time" => Mage::helper('adminhtml')->__('Time')
                         ),
          'required'  => true,
          'onchange' => 'optionExtended.onTypeChange();'                          
      ));


      $fieldset->addField('is_require', 'select', array(
          'name'      => 'is_require',
          'label'     => Mage::helper('core')->__('Required'),
          'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
          'value' => 1        
      ));


      $fieldset->addField('sort_order', 'text', array(
          'name'      => 'sort_order',
          'label'     => Mage::helper('core')->__('Sort Order')      
      ));


      $fieldset->addField('code', 'text', array(
          'name'      => 'code',
          'label'     => Mage::helper('core')->__('Code')     
      ));


      $disabled = false;
      $html = '';      
      if (!is_null(Mage::registry('current_option')->getId()) && Mage::registry('current_option')->getStoreId() != 0){
        $checked = '';
        if (is_null(Mage::registry('current_option')->getStoreNote())){
          $checked = 'checked="checked"';
          $disabled = true;          
        } 
       $html = '<input type="checkbox" id="note_use_default" class="checkbox" name="note_use_default" onclick="toggleValueElements(this, this.parentNode)" value="1" '.$checked.'/>&nbsp;'.
                         '<label class="normal" for="note_use_default">'.Mage::helper('adminhtml')->__('Use Default').'</label>';  
      }
     
      if ($this->getIsWysiwygEnabled()) {
        $editor = Mage::getSingleton('core/layout')
          ->createBlock('adminhtml/widget_button', '', array(
              'label'     => Mage::helper('catalog')->__('WYSIWYG Editor'),
              'type'      => 'button',
              'disabled'  => $disabled,
              'class'     => ($disabled) ? 'disabled' : '',
              'onclick'   => 'catalogWysiwygEditor.open(\''.Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/wysiwyg').'\', \'note\')'
          ))->toHtml();
        $html = $editor . '&nbsp;&nbsp;&nbsp;' . $html;
      }
      
      $fieldset->addField('note', 'textarea', array(
          'name'      => 'note',
          'label'     => $this->__('Note'),
          'disabled'  => $disabled,              
          'after_element_html' => $html                 
      ));


      $disabled = false;
      $useDefaultHtml = '';
      if (!is_null(Mage::registry('current_option')->getId()) && Mage::registry('current_option')->getStoreId() != 0){
        $checked = '';
        if (is_null(Mage::registry('current_option')->getStorePrice())){
          $checked = 'checked="checked"';
          $disabled = true;          
        } 
       $useDefaultHtml = '<input type="checkbox" id="price_use_default" class="checkbox" name="price_use_default" onclick="toggleValueElements(this, this.parentNode)" value="1" '.$checked.'/>&nbsp;'.
                         '<label class="normal" for="price_use_default">'.Mage::helper('adminhtml')->__('Use Default').'</label>';  
      }
      
      $fieldset->addField('price', 'text', array(
          'name'      => 'price',
          'label'     => Mage::helper('core')->__('Price'),
          'disabled'  => $disabled,              
          'after_element_html' => $useDefaultHtml                 
      ));

      
      $fieldset->addField('price_type', 'select', array(
          'name'      => 'price_type',
          'label'     => Mage::helper('core')->__('Price Type'),
          'options'   => array(
                           'fixed'   => Mage::helper('adminhtml')->__('Fixed'),
                           'percent' => Mage::helper('adminhtml')->__('Percent')
                         )                 
      ));     

       
      $fieldset->addField('sku', 'text', array(
          'name'      => 'sku',
          'label'     => Mage::helper('core')->__('Sku')      
      ));

            
      $fieldset->addField('max_characters', 'text', array(
          'name'      => 'max_characters',
          'label'     => Mage::helper('core')->__('Max Characters')      
      ));


      $fieldset->addField('file_extension', 'text', array(
          'name'      => 'file_extension',
          'label'     => Mage::helper('core')->__('File Extensions')      
      ));


      $fieldset->addField('image_size_x', 'text', array(
          'name'      => 'image_size_x',
          'label'     => Mage::helper('core')->__('Image Size X')      
      ));


      $fieldset->addField('image_size_y', 'text', array(
          'name'      => 'image_size_y',
          'label'     => Mage::helper('core')->__('Image Size Y')      
      ));


      $fieldset->addField('layout', 'select', array(
          'name'      => 'layout',
          'label'     => Mage::helper('core')->__('Layout'),
          'options'   => array(
                           'above'      =>$this->__('Above Option'),
                           'before'     =>$this->__('Before Option'),
                           'below'      =>$this->__('Below Option'),
                           'grid'       =>$this->__('Grid'),
                           'gridcompact'=>$this->__('Grid Compact'),                           
                           'list'       =>$this->__('List'),
                           'swap'       =>$this->__('Main Image'),
                           'picker'     =>$this->__('Color Picker'),
                           'pickerswap' =>$this->__('Picker & Main')
                         ),
          'onchange' => 'optionExtended.changePopup(this.value);'                                                         
      ));                                   


      $fieldset->addField('popup', 'checkbox', array(
          'name'      => 'popup',
          'label'     => Mage::helper('core')->__('Popup'),
          'value'     =>1,
          'checked'   => Mage::registry('current_option')->getPopup() == 1     
      )); 

               
      $fieldset->addField('row_id', 'hidden', array(
          'name' => 'row_id'              
      )); 


      $rows = Mage::registry('current_option')->getValueTitles();
      
      $options = array('' => '');
      foreach($rows as $row)
        $options[$row['row_id']] = $row['title'];
      
      $fieldset->addField('sd', 'select', array(
          'name'     => 'sd',
          'label'    => Mage::helper('core')->__('Selected By Default'),
          'values'   => $options                                                        
      ));         

      $options = array(array('value' => '-1', 'label' => ''));
      foreach($rows as $row)
        $options[] = array('value' => $row['row_id'], 'label' => $row['title']);
      
      $fieldset->addField('sd_multiple', 'multiselect', array(
          'name'      => 'sd_multiple[]',
          'label'     => Mage::helper('core')->__('Selected By Default'),
          'values'    => $options                                                    
      ));

      
      if (!is_null(Mage::registry('current_option')->getId()))         
        $form->setValues(Mage::registry('current_option')->getData());
                    
      $this->setForm($form);    
      return parent::_prepareForm();
  }
  
  
    public function getIsWysiwygEnabled()
    {
        $version = Mage::getVersion();
        return version_compare($version, '1.4.0.0') >= 0 && Mage::getSingleton('cms/wysiwyg_config')->isEnabled();
    } 
    
}
