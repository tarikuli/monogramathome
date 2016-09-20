<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Value_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{


  protected function _prepareLayout()
  {
      parent::_prepareLayout();
      if ($this->getIsWysiwygEnabled()) {
          $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);        
          $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
          $this->getLayout()->getBlock('js')->append($this->getLayout()->createBlock('core/template','catalog_wysiwyg_js', array('template'=>'catalog/wysiwyg/js.phtml', 'store'=>(int) $this->getRequest()->getParam('store'))));          
      }
  }

  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
              'id' => 'edit_form',
              'action' => $this->getUrl('*/*/save', array('_current'=>true)),
              'method' => 'post'
              ));
              
      $fieldset = $form->addFieldset('optionextended_form', array('legend'=>Mage::helper('adminhtml')->__('General Information')));

      $disabled = false;
      $useDefaultHtml = '';
      if (!is_null(Mage::registry('current_value')->getId()) && Mage::registry('current_value')->getStoreId() != 0){
        $checked = '';
        if (is_null(Mage::registry('current_value')->getStoreTitle())){
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

      $disabled = false;
      $useDefaultHtml = '';
      if (!is_null(Mage::registry('current_value')->getId()) && Mage::registry('current_value')->getStoreId() != 0){
        $checked = '';
        if (is_null(Mage::registry('current_value')->getStorePrice())){
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



      $fieldset->addField('sort_order', 'text', array(
          'name'      => 'sort_order',
          'label'     => Mage::helper('core')->__('Sort Order')      
      ));


      $fieldset->addType('text', Mage::getConfig()->getBlockClassName('optionextended/optiontemplate_value_helper_form_text'));                    
                 
      $fieldset->addField('children', 'text', array(
          'id'        => 'optionextended_children',
          'name'      => 'children',
          'label'     => Mage::helper('core')->__('Children'),
          'onblur'    => 'optionExtended.checkChildren(this)',
          'style'     => 'width:235px'      
      ));


    
      $fieldset->addType('image', Mage::getConfig()->getBlockClassName('optionextended/optiontemplate_value_helper_form_image'));                    
      
      $fieldset->addField('image', 'image', array(
          'name'      => 'image',
          'label'     => Mage::helper('core')->__('Image')                 
      ));



      $disabled = false;
      $html = '';      
      if (!is_null(Mage::registry('current_value')->getId()) && Mage::registry('current_value')->getStoreId() != 0){
        $checked = '';
        if (is_null(Mage::registry('current_value')->getStoreDescription())){
          $checked = 'checked="checked"';
          $disabled = true;          
        } 
       $html = '<input type="checkbox" id="description_use_default" class="checkbox" name="description_use_default" onclick="toggleValueElements(this, this.parentNode)" value="1" '.$checked.'/>&nbsp;'.
                         '<label class="normal" for="description_use_default">'.Mage::helper('adminhtml')->__('Use Default').'</label>';  
      }
        
      if ($this->getIsWysiwygEnabled()) {
        $editor = Mage::getSingleton('core/layout')
          ->createBlock('adminhtml/widget_button', '', array(
              'label'     => Mage::helper('catalog')->__('WYSIWYG Editor'),
              'type'      => 'button',
              'disabled'  => $disabled,
              'class'     => ($disabled) ? 'disabled' : '',
              'onclick'   => 'catalogWysiwygEditor.open(\''.Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/wysiwyg').'\', \'description\')'
          ))->toHtml();
        $html = $editor . '&nbsp;&nbsp;&nbsp;' . $html;
      }


      $fieldset->addField('description', 'textarea', array(
          'name'      => 'description',
          'label'     => Mage::helper('core')->__('Description'),
          'disabled'  => $disabled,              
          'after_element_html' => $html                 
      ));


      $fieldset->addField('row_id', 'hidden', array(
          'name' => 'row_id'              
      ));
      
      $form->setValues(Mage::registry('current_value')->getData());
      $form->setUseContainer(true); 
      $this->setForm($form);
          
      return parent::_prepareForm();
  }
  
    public function getIsWysiwygEnabled()
    {
        return version_compare(Mage::getVersion(), '1.4.0.0') >= 0 && Mage::getSingleton('cms/wysiwyg_config')->isEnabled();
    } 
}
