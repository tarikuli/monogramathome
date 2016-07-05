<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Value_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('optionextendedgrid');
      $this->setDefaultSort('sort_order');
      $this->setDefaultDir('asc');    
      $this->setUseAjax(true);             
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('optionextended/template_value')
      ->getCollection()
      ->joinTitle(0)
      ->joinPrice(0)       
      ->joinDescription(0)             
      ->addFieldToFilter('option_id', (int) $this->getRequest()->getParam('option_id'));
      
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }


  protected function _afterLoadCollection()
  {    
      // to fix magento 161 invalid foreach argument warning because of ambigous "children" word
      //magento/app/design/adminhtml/default/default/template/widget/grid.phtml
      //line 171
      //magento/app/code/core/Mage/Adminhtml/Block/Widget/Grid.php
      //line 1555
      foreach ($this->getCollection() as $item){
        $item->setChildrenIds($item->getChildren());
        $item->setChildren(null);
      }
 
      return parent::_afterLoadCollection();
  }


  protected function _prepareColumns()
  {
      $this->addColumn('row_id', array(
          'header'    => $this->__('Row Id'),
          'align'     =>'left', 	
          'index'     => 'row_id',
          'width'     => '60'              
      ));     

      
      $this->addColumn('title', array(
          'header'    => Mage::helper('catalog')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));
    
      $this->addColumn('price', array(
          'header'    => Mage::helper('catalog')->__('Price'),
          'align'     =>'left', 	
          'index'     => 'price',
          'width'     => '50'                     
      ));
      
      $this->addColumn('price_type', array(
          'header'    => Mage::helper('catalog')->__('Price Type'),
          'align'     =>'left',
          'width'     => '70',             	
          'index'     => 'price_type',
          'type'      => 'options',
          'options'   => array(
                           'fixed'   => Mage::helper('adminhtml')->__('Fixed'),
                           'percent' => Mage::helper('adminhtml')->__('Percent')
                         )                   
      ));
        
      $this->addColumn('sku', array(
          'header'    => Mage::helper('sales')->__('Sku'),
          'align'     =>'left',
          'index'     => 'sku',
          'width'     => '140'         
      ));

      $this->addColumn('children_ids', array(
          'header'    => $this->__('Children'),
          'align'     =>'left', 	
          'width'     => '150',
          'html_decorators' => array('nobr') ,                         
          'index'     => 'children_ids',
          'type'      => 'text',
          'truncate'  => 25         
                   
      ));
   
      $this->addColumn('image', array(
          'header'    => Mage::helper('catalog')->__('Image'),
          'align'     =>'center', 	
          'width'     => '45',           
          'index'     => 'image',
          'renderer'   => 'optionextended/optiontemplate_value_grid_renderer_image'                     
      ));      

    
      $this->addColumn('description', array(
          'header'    => Mage::helper('adminhtml')->__('Description'),
          'align'     =>'left',
          'width'     => '150',  
          'html_decorators' => array('nobr') ,                         	
          'index'     => 'description',
          'type'      => 'text',
          'truncate'  => 25,
          'escape'    => true                     
      ));

      $this->addColumn('sort_order', array(
          'header'    => Mage::helper('adminhtml')->__('Sort Order'),
          'align'     => 'left', 
          'width'     => '60',          	
          'index'     => 'sort_order'
      ));
     
        
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('adminhtml')->__('Action'),
                'width'     => '40',               
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('adminhtml')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit','params'=>array('_current'=>true)),
                        'field'     => 'value_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
        ));        
	  
      return parent::_prepareColumns();
  }

  protected function _prepareMassaction()
  {
      $this->setMassactionIdField('value_id');
      $this->getMassactionBlock()->setFormFieldName('ids');

      $this->getMassactionBlock()->addItem('delete', array(
           'label'    => Mage::helper('adminhtml')->__('Delete'),
           'url'      => $this->getUrl('*/*/massDelete', array('_current'=>true)),
           'confirm'  => Mage::helper('adminnotification')->__('Are you sure?')
      ));
	
      return $this;
  }
  
  public function getGridUrl()
  {
      return $this->getUrl('*/*/grid', array('_current'=>true));
  }
  
  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('value_id' => $row->getId(), '_current'=>true));
  }
  

  
}
