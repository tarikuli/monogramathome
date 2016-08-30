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
 * Contact admin edit tabs
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author      Ultimate Module Creator
 */
class Julfiker_Contact_Block_Adminhtml_Ambassadorqueue_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
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
        $this->setId('contact_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('julfiker_contact')->__('Contact'));
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
            'form_contact',
            array(
                'label'   => Mage::helper('julfiker_contact')->__('Contact'),
                'title'   => Mage::helper('julfiker_contact')->__('Contact'),
                'content' => $this->getLayout()->createBlock(
                    'julfiker_contact/adminhtml_ambassadorqueue_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve contact entity
     *
     * @access public
     * @return Julfiker_Contact_Model_Contact
     * @author Ultimate Module Creator
     */
    public function getAmbassadorqueue()
    {
        return Mage::registry('current_ambassadorqueue');
    }
}
