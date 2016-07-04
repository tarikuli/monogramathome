<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Option_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
      $collection = Mage::getModel('optionextended/template_option')
      ->getCollection()
      ->joinTitle()
      ->joinPrice()         
      ->joinNote()                
      ->addFieldToFilter('template_id', (int) $this->getRequest()->getParam('template_id'));
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {

      $this->addColumn('title', array(
          'header'    => Mage::helper('catalog')->__('Title'),
          'align'     =>'left',
          'index'     => 'title'                    
      ));
      
      $this->addColumn('code', array(
          'header'    => Mage::helper('core')->__('Code'),
          'align'     =>'left', 
          'width'     => '150',                      	
          'index'     => 'code'        
      ));

      
      $this->addColumn('type', array(
          'header'    => Mage::helper('adminhtml')->__('Type'),
          'align'     =>'left',
          'width'     => '110',           
          'html_decorators' => array('nobr') ,                         	
          'index'     => 'type',
          'type'      => 'options',
          'options'   => array(
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
                         )                          
      ));
                      



      $this->addColumn('is_require', array(
          'header'    => Mage::helper('adminhtml')->__('Required'),
          'align'     =>'left',
          'width'     => '70',            	
          'index'     => 'is_require',
          'type'      => 'options',
          'options'   => array(
                          0 => Mage::helper('adminhtml')->__('No'),
                          1 => Mage::helper('adminhtml')->__('Yes')
                         )           
    
      ));

      
       $this->addColumn('sort_order', array(
          'header'    => Mage::helper('adminhtml')->__('Sort Order'),
          'align'     => 'left', 	
          'index'     => 'sort_order',
          'width'     => '60',                       
      ));   

        
      $this->addColumn('note', array(
          'header'    => $this->__('Note'),
          'align'     => 'left', 
          'width'     => '150',            	
          'index'     => 'note',
          'html_decorators' => array('nobr') ,          
          'type'      => 'text',
          'truncate'  => 25,
          'escape'    => true                       
      ));
      
      $valueCount =  Mage::getResourceModel('optionextended/template_option')->getGridValueCount((int) $this->getRequest()->getParam('template_id'));                                 
      
      $this->addColumn('action',
          array(
              'header'     =>  $this->__('Values'),
              'width'      => '110',                               
              'filter'     => false,
              'sortable'   => false,
              'only_values'=> true,
              'renderer'   => 'optionextended/optiontemplate_option_grid_renderer_action',
              'value_count'=> $valueCount
      ));
        
      $this->addColumn('layout', array(
          'header'    => Mage::helper('core')->__('Layout'),
          'align'     =>'left', 
          'width'     => '110',           
          'html_decorators' => array('nobr') ,           	
          'index'     => 'layout',
          'type'      => 'options',          
          'renderer'  => 'optionextended/optiontemplate_option_grid_renderer_options',  
          'options'   => array(
                          'above'      =>$this->__('Above Option'),        
                          'before'     =>$this->__('Before Option'),
                          'below'      =>$this->__('Below Option'),
                          'swap'       =>$this->__('Main Image'),            
                          'grid'       =>$this->__('Grid'), 
                          'gridcompact'=>$this->__('Grid Compact'),                               
                          'list'       =>$this->__('List'),  
                          'picker'     =>$this->__('Color Picker'), 
                          'pickerswap' =>$this->__('Picker & Main') 
                         )           
    
      ));

      $this->addColumn('popup', array(
          'header'    => $this->__('Popup'),
          'align'     =>'left', 	
          'index'     => 'popup',
          'width'     => '60',            
          'type'      => 'options',            
          'renderer'  => 'optionextended/optiontemplate_option_grid_renderer_options',          
          'options'   => array(
                          0 => Mage::helper('adminhtml')->__('No'),
                          1 => Mage::helper('adminhtml')->__('Yes')
                         )           
    
      ));
            
      $this->addColumn('selected_by_default', array(
          'header'    => $this->__('Selected By Default'),
          'align'     =>'left', 	
          'index'     => 'selected_by_default',
          'width'     => '130',
          'type'      => 'text',
          'truncate'  => 20                               
      ));

      $this->addColumn('row_id', array(
          'header'    => $this->__('Row Id'),
          'align'     =>'left', 	
          'index'     => 'row_id',
          'width'     => '60',                     
      ));
           
      $this->addColumn('price', array(
          'header'    => Mage::helper('adminhtml')->__('Price'),
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
          'width'     => '90'          
      ));

      $this->addColumn('max_characters', array(
          'header'    => Mage::helper('catalog')->__('Max Characters'),
          'align'     =>'left',
          'index'     => 'max_characters',
          'width'     => '90'          
      ));

      $this->addColumn('file_extension', array(
          'header'    => $this->__('File Extensions'),
          'align'     =>'left',
          'index'     => 'file_extension',
          'width'     => '90'          
      ));

      $this->addColumn('image_size_x', array(
          'header'    => $this->__('Image Size X'),
          'align'     =>'left',
          'index'     => 'image_size_x',
          'width'     => '90'          
      ));

      $this->addColumn('image_size_y', array(
          'header'    => $this->__('Image Size Y'),
          'align'     =>'left',
          'index'     => 'image_size_y',
          'width'     => '90'          
      ));                  

      
        $this->addColumn('action2',
            array(
                'header'    =>  Mage::helper('adminhtml')->__('Action'),
                'width'     => '150',                               
                'filter'    => false,
                'sortable'  => false,
                'renderer'  => 'optionextended/optiontemplate_option_grid_renderer_action',
                'value_count'=> $valueCount
        ));
	  
      return parent::_prepareColumns();
  }

  protected function _prepareMassaction()
  {
      $this->setMassactionIdField('option_id');
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
      return $this->getUrl('*/*/edit', array('option_id' => $row->getId(), '_current'=>true));
  }

}
