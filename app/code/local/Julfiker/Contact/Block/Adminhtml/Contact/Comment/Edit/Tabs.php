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
 * Contact comment admin edit tabs
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author      Ultimate Module Creator
 */
class Julfiker_Contact_Block_Adminhtml_Contact_Comment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('contact_comment_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('julfiker_contact')->__('Contact Comment'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return Julfiker_Contact_Block_Adminhtml_Contact_Edit_Tabs
     * @author Ultimate Module Creator
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_contact_comment',
            array(
                'label'   => Mage::helper('julfiker_contact')->__('Contact comment'),
                'title'   => Mage::helper('julfiker_contact')->__('Contact comment'),
                'content' => $this->getLayout()->createBlock(
                    'julfiker_contact/adminhtml_contact_comment_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addTab(
                'form_store_contact_comment',
                array(
                    'label'   => Mage::helper('julfiker_contact')->__('Store views'),
                    'title'   => Mage::helper('julfiker_contact')->__('Store views'),
                    'content' => $this->getLayout()->createBlock(
                        'julfiker_contact/adminhtml_contact_comment_edit_tab_stores'
                    )
                    ->toHtml(),
                )
            );
        }
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve comment
     *
     * @access public
     * @return Julfiker_Contact_Model_Contact_Comment
     * @author Ultimate Module Creator
     */
    public function getComment()
    {
        return Mage::registry('current_comment');
    }
}
