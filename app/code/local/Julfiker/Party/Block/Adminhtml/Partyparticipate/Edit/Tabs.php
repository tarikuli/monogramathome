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
 * Party_participate admin edit tabs
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Block_Adminhtml_Partyparticipate_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
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
        $this->setId('partyparticipate_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('julfiker_party')->__('Party_participate'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return Julfiker_Party_Block_Adminhtml_Partyparticipate_Edit_Tabs
     * @author Julfiker
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_partyparticipate',
            array(
                'label'   => Mage::helper('julfiker_party')->__('Party_participate'),
                'title'   => Mage::helper('julfiker_party')->__('Party_participate'),
                'content' => $this->getLayout()->createBlock(
                    'julfiker_party/adminhtml_partyparticipate_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve party_participate entity
     *
     * @access public
     * @return Julfiker_Party_Model_Partyparticipate
     * @author Julfiker
     */
    public function getPartyparticipate()
    {
        return Mage::registry('current_partyparticipate');
    }
}
