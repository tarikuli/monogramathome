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
 * Party order item admin edit tabs
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Block_Adminhtml_Partyorderitem_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
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
        $this->setId('partyorderitem_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('julfiker_party')->__('Party order item'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return Julfiker_Party_Block_Adminhtml_Partyorderitem_Edit_Tabs
     * @author Julfiker
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_partyorderitem',
            array(
                'label'   => Mage::helper('julfiker_party')->__('Party order item'),
                'title'   => Mage::helper('julfiker_party')->__('Party order item'),
                'content' => $this->getLayout()->createBlock(
                    'julfiker_party/adminhtml_partyorderitem_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addTab(
                'form_store_partyorderitem',
                array(
                    'label'   => Mage::helper('julfiker_party')->__('Store views'),
                    'title'   => Mage::helper('julfiker_party')->__('Store views'),
                    'content' => $this->getLayout()->createBlock(
                        'julfiker_party/adminhtml_partyorderitem_edit_tab_stores'
                    )
                    ->toHtml(),
                )
            );
        }
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve party order item entity
     *
     * @access public
     * @return Julfiker_Party_Model_Partyorderitem
     * @author Julfiker
     */
    public function getPartyorderitem()
    {
        return Mage::registry('current_partyorderitem');
    }
}
