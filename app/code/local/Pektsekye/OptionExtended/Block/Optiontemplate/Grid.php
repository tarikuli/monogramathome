<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('optionextendedgrid');
      $this->setUseAjax(true);

  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('optionextended/template')->getCollection();       
      $this->setCollection($collection);      
      parent::_prepareCollection();
      return $this;
  }

  protected function _prepareColumns()
  {

      $this->addColumn('title', array(
          'header'    => Mage::helper('adminhtml')->__('Template Name'),
          'align'     =>'left',
          'index'     => 'title'            
      ));
	  
      $this->addColumn('code', array(
          'header'    => Mage::helper('core')->__('Code'),
          'align'     =>'left',
          'width'     => 150,               	
          'index'     => 'code'          
      ));
      
      $this->addColumn('product_ids', array(
          'header'     => Mage::helper('adminhtml')->__('Products'),
          'align'      =>'left',
          'width'      => 150,  
          'html_decorators' => array('nobr') ,                     
          'filter'     => false,
          'sortable'   => false,
          'product_ids'=> Mage::getResourceModel('optionextended/template')->getGridProductIds(),
          'renderer'   => 'optionextended/optiontemplate_grid_renderer_text'    
      )); 
           
      $this->addColumn('is_active', array(
          'header'    => Mage::helper('adminhtml')->__('Status'),
          'align'     =>'left', 	
          'width'     => 80,            
          'index'     => 'is_active',
          'type'      => 'options',
          'options'   => array(
              1 => Mage::helper('adminhtml')->__('Enabled'),          
              0 => Mage::helper('adminhtml')->__('Disabled'))          
      ));
      
        $this->addColumn('action',
            array(
                'header'      =>  Mage::helper('adminhtml')->__('Action'),
                'width'       => 150,                 
                'filter'      => false,
                'sortable'    => false,
                'renderer'    => 'optionextended/optiontemplate_grid_renderer_action',
                'option_count'=> Mage::getResourceModel('optionextended/template')->getGridOptionCount()                                
        ));

	  
      return parent::_prepareColumns();
  }

  protected function _prepareMassaction()
  {
      $this->setMassactionIdField('template_id');
      $this->getMassactionBlock()->setFormFieldName('ids');

      $this->getMassactionBlock()->addItem('delete', array(
           'label'    => Mage::helper('adminhtml')->__('Delete'),
           'url'      => $this->getUrl('*/*/massDelete'),
           'confirm'  => Mage::helper('adminnotification')->__('Are you sure?')
      ));
      
      $this->getMassactionBlock()->addItem('status', array(
           'label'=> Mage::helper('catalog')->__('Change status'),
           'url'  => $this->getUrl('*/*/massStatus'),
           'additional' => array(
                  'visibility' => array(
                       'name'     => 'status',
                       'type'     => 'select',
                       'class'    => 'required-entry',
                       'label'    => Mage::helper('adminhtml')->__('Status'),
                       'values'   => array(
                                      1 => Mage::helper('adminhtml')->__('Enabled'),          
                                      0 => Mage::helper('adminhtml')->__('Disabled'))
                                     )
           )
      ));	
      return $this;
  }
  
  public function getGridUrl()
  {
      return $this->getUrl('*/*/grid', array('_current'=>true));
  }
    
  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('template_id' => $row->getId()));
  }

}
