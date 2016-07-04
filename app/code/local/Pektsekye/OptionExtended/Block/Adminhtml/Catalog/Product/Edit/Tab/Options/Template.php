<?php


class Pektsekye_OptionExtended_Block_Adminhtml_Catalog_Product_Edit_Tab_Options_Template extends Mage_Adminhtml_Block_Widget
{   
    protected $_appliedTemplates;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('optionextended/catalog/product/edit/options/template.phtml');                 
    }


    protected function _prepareLayout()
    {

        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('adminhtml')->__('Remove'),
                    'class' => 'delete icon-btn'
                ))
        );

        $this->setChild('apply_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => $this->__('Apply'),
                    'onclick'   => 'optionExtended.applyTemplate()'                    
                ))
        );   

        $this->setChild('use_template_options_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => $this->__('Insert'),
                    'onclick'   => 'optionExtended.insertTemplateOptions()'                    
                ))
        ); 
             
        return parent::_prepareLayout();
    }



    public function getDeleteButtonHtml($templateId)
    {
        return $this->getChild('delete_button')
          ->setData('onclick', 'optionExtended.removeTemplate('.$templateId.')')
          ->toHtml();
    }	
    
    public function getApplyButtonHtml()
    {
        return $this->getChildHtml('apply_button');
    }

    public function getUseTemplateOptionsButtonHtml()
    {
        return $this->getChildHtml('use_template_options_button');
    }

 

    public function getTemplateSelect()
    {    

      $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => 'optionextended_template_select',
                'class' => 'optionextended-template-select'                      
            ))           
            ->setName('optionextended_template_select')            
            ->setOptions(Mage::getResourceModel('optionextended/template')->getTemplatesAsOptionArray($this->getProductId()));
     
      return $select->getHtml();                
    }


    public function getAppliedTemplates()
    {
      if (!isset($this->_appliedTemplates))
        $this->_appliedTemplates = Mage::getResourceModel('optionextended/template')->getAppliedTemplates((int) $this->getProductId()); 
        
      return $this->_appliedTemplates;   
    }

    
    public function getOptionsUrl($templateId)
    {
        return $this->getUrl('*/optiontemplate_option/index', array('template_id' => (int) $templateId));                          
    }


    public function getTemplateIdsString()
    {
     $idsString = '';
      foreach ($this->getAppliedTemplates() as $row)
        $idsString .= ($idsString != '' ? ',' : '') . $row['template_id'];
      return $idsString;    
    }    
        
}
