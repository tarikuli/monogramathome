<?php

class Infinite_ImageGallery_Block_Adminhtml_Image_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $collection = Mage::getModel('imagegallery/image')->getCollection();

        $galleryId = Mage::getSingleton('adminhtml/session')->getGalleryId();
        if(isset($galleryId))
            $collection->addFieldToFilter('gallery_id', $galleryId);

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

        $this->addColumn('title', array(
            'header'    => Mage::helper('imagegallery')->__('Title'),
            'align'     =>'left',
            'index'     => 'title',
        ));

        $this->addColumn('description', array(
            'header'    => Mage::helper('imagegallery')->__('Description'),
            'align'     =>'left',
            'index'     => 'description',
        ));

        $this->addColumn('image', array(
            'header'    => Mage::helper('imagegallery')->__('Image'),
            'align'     =>'left',
            'index'     => 'image',
        ));	 

        $this->addColumn('image_row', array(
            'header'    => Mage::helper('imagegallery')->__('Row Number'),
            'align'     =>'center',
            'index'     => 'image_row',
            'width'     => '80px',
        ));

        $this->addColumn('image_order', array(
            'header'    => Mage::helper('imagegallery')->__('Order'),
            'align'     =>'center',
            'index'     => 'image_order',
            'width'     => '80px',
        ));

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('imagegallery')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => Mage::getSingleton('imagegallery/image')->getStatusArray(),
        ));
	  
        return parent::_prepareColumns();
    }


    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}