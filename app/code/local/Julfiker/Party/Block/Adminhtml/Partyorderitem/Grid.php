<?php
/**
 * Julfiker_Party extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Julfiker
 * @package        Julfiker_Party
 * @copyright      Copyright (c) 2017
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Party order item admin grid block
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Block_Adminhtml_Partyorderitem_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     *
     * @access public
     * @author Julfiker
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('partyorderitemGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return Julfiker_Party_Block_Adminhtml_Partyorderitem_Grid
     * @author Julfiker
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('julfiker_party/partyorderitem')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return Julfiker_Party_Block_Adminhtml_Partyorderitem_Grid
     * @author Julfiker
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                'header' => Mage::helper('julfiker_party')->__('Id'),
                'index'  => 'entity_id',
                'type'   => 'number'
            )
        );
        $this->addColumn(
            'event_id',
            array(
                'header'    => Mage::helper('julfiker_party')->__('Event'),
                'index'     => 'event_id',
                'type'      => 'options',
                'options'   => Mage::getResourceModel('julfiker_party/event_collection')
                    ->toOptionHash(),
                'renderer'  => 'julfiker_party/adminhtml_helper_column_renderer_parent',
                'params'    => array(
                    'id'    => 'getEventId'
                ),
                'base_link' => 'adminhtml/party_event/edit'
            )
        );
        $this->addColumn(
            'order_id',
            array(
                'header'    => Mage::helper('julfiker_party')->__('Order Id'),
                'align'     => 'left',
                'index'     => 'order_id',
            )
        );
        
        $this->addColumn(
            'status',
            array(
                'header'  => Mage::helper('julfiker_party')->__('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'options' => array(
                    '1' => Mage::helper('julfiker_party')->__('Enabled'),
                    '0' => Mage::helper('julfiker_party')->__('Disabled'),
                )
            )
        );
        if (!Mage::app()->isSingleStoreMode() && !$this->_isExport) {
            $this->addColumn(
                'store_id',
                array(
                    'header'     => Mage::helper('julfiker_party')->__('Store Views'),
                    'index'      => 'store_id',
                    'type'       => 'store',
                    'store_all'  => true,
                    'store_view' => true,
                    'sortable'   => false,
                    'filter_condition_callback'=> array($this, '_filterStoreCondition'),
                )
            );
        }
        $this->addColumn(
            'created_at',
            array(
                'header' => Mage::helper('julfiker_party')->__('Created at'),
                'index'  => 'created_at',
                'width'  => '120px',
                'type'   => 'datetime',
            )
        );
        $this->addColumn(
            'updated_at',
            array(
                'header'    => Mage::helper('julfiker_party')->__('Updated at'),
                'index'     => 'updated_at',
                'width'     => '120px',
                'type'      => 'datetime',
            )
        );
        $this->addColumn(
            'action',
            array(
                'header'  =>  Mage::helper('julfiker_party')->__('Action'),
                'width'   => '100',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('julfiker_party')->__('Edit'),
                        'url'     => array('base'=> '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'is_system' => true,
                'sortable'  => false,
            )
        );
        $this->addExportType('*/*/exportCsv', Mage::helper('julfiker_party')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('julfiker_party')->__('Excel'));
        $this->addExportType('*/*/exportXml', Mage::helper('julfiker_party')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * prepare mass action
     *
     * @access protected
     * @return Julfiker_Party_Block_Adminhtml_Partyorderitem_Grid
     * @author Julfiker
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('partyorderitem');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'=> Mage::helper('julfiker_party')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('julfiker_party')->__('Are you sure?')
            )
        );
        $this->getMassactionBlock()->addItem(
            'status',
            array(
                'label'      => Mage::helper('julfiker_party')->__('Change status'),
                'url'        => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                    'status' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('julfiker_party')->__('Status'),
                        'values' => array(
                            '1' => Mage::helper('julfiker_party')->__('Enabled'),
                            '0' => Mage::helper('julfiker_party')->__('Disabled'),
                        )
                    )
                )
            )
        );
        $values = Mage::getResourceModel('julfiker_party/event_collection')->toOptionHash();
        $values = array_reverse($values, true);
        $values[''] = '';
        $values = array_reverse($values, true);
        $this->getMassactionBlock()->addItem(
            'event_id',
            array(
                'label'      => Mage::helper('julfiker_party')->__('Change Event'),
                'url'        => $this->getUrl('*/*/massEventId', array('_current'=>true)),
                'additional' => array(
                    'flag_event_id' => array(
                        'name'   => 'flag_event_id',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('julfiker_party')->__('Event'),
                        'values' => $values
                    )
                )
            )
        );
        return $this;
    }

    /**
     * get the row url
     *
     * @access public
     * @param Julfiker_Party_Model_Partyorderitem
     * @return string
     * @author Julfiker
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
     * @author Julfiker
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * after collection load
     *
     * @access protected
     * @return Julfiker_Party_Block_Adminhtml_Partyorderitem_Grid
     * @author Julfiker
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
     * @param Julfiker_Party_Model_Resource_Partyorderitem_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Julfiker_Party_Block_Adminhtml_Partyorderitem_Grid
     * @author Julfiker
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
