<?php

class Infinite_ImageGallery_Block_Adminhtml_Gallery_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('gridGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('imagegallery/gallery')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('imagegallery')->__('ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'entity_id',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('imagegallery')->__('Name'),
            'align'     =>'left',
            'index'     => 'name',
        ));

        $this->addColumn('identifier', array(
            'header'    => Mage::helper('imagegallery')->__('Identifier'),
            'align'     =>'left',
            'index'     => 'identifier',
        ));

        /* $this->addColumn('no_of_images_per_row', array(
            'header'    => Mage::helper('imagegallery')->__('No of Images Per Row'),
            'align'     =>'left',
            'index'     => 'no_of_images_per_row',
        )); */

        $this->addColumn('height_of_icon', array(
            'header'    => Mage::helper('imagegallery')->__('Height of Icons'),
            'align'     =>'left',
            'index'     => 'height_of_icon',
        ));

        $this->addColumn('creation_time', array(
            'header'    => Mage::helper('imagegallery')->__('Created Time'),
            'align'     =>'left',
            'type'      =>'date',
            'index'     => 'creation_time',
        ));

        $this->addColumn('update_time', array(
            'header'    => Mage::helper('imagegallery')->__('Updated Time'),
            'align'     =>'left',
            'type'      =>'date',
            'index'     => 'update_time',
        ));

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('imagegallery')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => Mage::getSingleton('imagegallery/gallery')->getStatusArray(),
        ));
	  
        $this->addColumn('manage_slides',
            array(
                'header'    =>  Mage::helper('imagegallery')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('imagegallery')->__('Manage Images'),
                        'url'       => array('base'=> '*/image/index'),
                        'field'     => 'gallery_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('imagegallery')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('imagegallery')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
	  
        return parent::_prepareColumns();
    }


    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}