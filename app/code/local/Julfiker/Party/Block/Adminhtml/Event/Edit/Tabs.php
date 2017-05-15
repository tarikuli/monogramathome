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
 * Event admin edit tabs
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Block_Adminhtml_Event_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     * @author Julfiker
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('event_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('julfiker_party')->__('Event'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return Julfiker_Party_Block_Adminhtml_Event_Edit_Tabs
     * @author Julfiker
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_event',
            array(
                'label'   => Mage::helper('julfiker_party')->__('Event'),
                'title'   => Mage::helper('julfiker_party')->__('Event'),
                'content' => $this->getLayout()->createBlock(
                    'julfiker_party/adminhtml_event_edit_tab_form'
                )
                ->toHtml(),
            )
        );

        $this->addTab(
            'event_coupon_form',
            array(
                'label'   => Mage::helper('julfiker_party')->__('Coupon Or Voucher'),
                'title'   => Mage::helper('julfiker_party')->__('Coupon Or Voucher'),
                'content' => $this->getLayout()->createBlock(
                    'julfiker_party/adminhtml_event_edit_tab_coupon'
                )
                ->toHtml(),
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addTab(
                'form_store_event',
                array(
                    'label'   => Mage::helper('julfiker_party')->__('Store views'),
                    'title'   => Mage::helper('julfiker_party')->__('Store views'),
                    'content' => $this->getLayout()->createBlock(
                        'julfiker_party/adminhtml_event_edit_tab_stores'
                    )
                    ->toHtml(),
                )
            );
        }
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve event entity
     *
     * @access public
     * @return Julfiker_Party_Model_Event
     * @author Julfiker
     */
    public function getEvent()
    {
        return Mage::registry('current_event');
    }
}
