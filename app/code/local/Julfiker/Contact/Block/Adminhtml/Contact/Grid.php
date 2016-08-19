<?php
/**
 * Julfiker_Contact extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Julfiker
 * @package        Julfiker_Contact
 * @copyright      Copyright (c) 2016
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Contact admin grid block
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author      Ultimate Module Creator
 */
class Julfiker_Contact_Block_Adminhtml_Contact_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('contactGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return Julfiker_Contact_Block_Adminhtml_Contact_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('julfiker_contact/contact')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return Julfiker_Contact_Block_Adminhtml_Contact_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                'header' => Mage::helper('julfiker_contact')->__('Id'),
                'index'  => 'entity_id',
                'type'   => 'number'
            )
        );

        $this->addColumn(
            'contact_type',
            array(
                'header'  => Mage::helper('julfiker_contact')->__('Contact type'),
                'index'   => 'contact_type',
                'type'    => 'options',
                'options' => Mage::getModel('julfiker_contact/contact_attribute_source_contacttype')->getGridOptions()            )
         );

        $this->addColumn(
            'status',
            array(
                'header'  => Mage::helper('julfiker_contact')->__('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'options' => array(
                    '1' => Mage::helper('julfiker_contact')->__('Pending - LDM'),
                    '2' => Mage::helper('julfiker_contact')->__('Pending - Email'),
                    '3' => Mage::helper('julfiker_contact')->__('Scheduled Party'),
                    '4' => Mage::helper('julfiker_contact')->__('Recruited'),

                )
            )
        );
        $this->addColumn(
            'first_name',
            array(
                'header' => Mage::helper('julfiker_contact')->__('First Name'),
                'index'  => 'first_name',
                'type'=> 'text',

            )
        );
        $this->addColumn(
            'last_name',
            array(
                'header' => Mage::helper('julfiker_contact')->__('Last Name'),
                'index'  => 'last_name',
                'type'=> 'text',

            )
        );
        $this->addColumn(
            'email',
            array(
                'header' => Mage::helper('julfiker_contact')->__('Email'),
                'index'  => 'email',
                'type'=> 'text',

            )
        );
        $this->addColumn(
            'phone',
            array(
                'header' => Mage::helper('julfiker_contact')->__('Phone'),
                'index'  => 'phone',
                'type'=> 'text',

            )
        );
        $this->addColumn(
            'zipcode',
            array(
                'header' => Mage::helper('julfiker_contact')->__('Zip code'),
                'index'  => 'zipcode',
                'type'=> 'text',

            )
        );

        $this->addColumn(
            'created_at',
            array(
                'header' => Mage::helper('julfiker_contact')->__('Created at'),
                'index'  => 'created_at',
                'width'  => '120px',
                'type'   => 'datetime',
            )
        );
        $this->addColumn(
            'updated_at',
            array(
                'header'    => Mage::helper('julfiker_contact')->__('Updated at'),
                'index'     => 'updated_at',
                'width'     => '120px',
                'type'      => 'datetime',
            )
        );
        $this->addColumn(
            'action',
            array(
                'header'  =>  Mage::helper('julfiker_contact')->__('Action'),
                'width'   => '100',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('julfiker_contact')->__('Edit'),
                        'url'     => array('base'=> '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'is_system' => true,
                'sortable'  => false,
            )
        );
        $this->addExportType('*/*/exportCsv', Mage::helper('julfiker_contact')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('julfiker_contact')->__('Excel'));
        $this->addExportType('*/*/exportXml', Mage::helper('julfiker_contact')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * prepare mass action
     *
     * @access protected
     * @return Julfiker_Contact_Block_Adminhtml_Contact_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('contact');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'=> Mage::helper('julfiker_contact')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('julfiker_contact')->__('Are you sure?')
            )
        );

//        $this->getMassactionBlock()->addItem(
//            'status',
//            array(
//                'label'      => Mage::helper('julfiker_contact')->__('Change status'),
//                'url'        => $this->getUrl('*/*/massStatus', array('_current'=>true)),
//                'additional' => array(
//                    'status' => array(
//                        'name'   => 'status',
//                        'type'   => 'select',
//                        'class'  => 'required-entry',
//                        'label'  => Mage::helper('julfiker_contact')->__('Status'),
//                        'values' => array(
//                            '1' => Mage::helper('julfiker_contact')->__('Enabled'),
//                            '0' => Mage::helper('julfiker_contact')->__('Disabled'),
//                        )
//                    )
//                )
//            )
//        );
        return $this;
    }

    /**
     * get the row url
     *
     * @access public
     * @param Julfiker_Contact_Model_Contact
     * @return string
     * @author Ultimate Module Creator
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * get the grid url
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * after collection load
     *
     * @access protected
     * @return Julfiker_Contact_Block_Adminhtml_Contact_Grid
     * @author Ultimate Module Creator
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /**
     * filter store column
     *
     * @access protected
     * @param Julfiker_Contact_Model_Resource_Contact_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Julfiker_Contact_Block_Adminhtml_Contact_Grid
     * @author Ultimate Module Creator
     */
    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->addStoreFilter($value);
        return $this;
    }
}
