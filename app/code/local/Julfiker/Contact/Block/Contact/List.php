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
 * Contact list block
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author Ultimate Module Creator
 */
class Julfiker_Contact_Block_Contact_List extends Mage_Core_Block_Template
{
    /**
     * initialize
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function _construct()
    {
        parent::_construct();
        $contacts = Mage::getResourceModel('julfiker_contact/contact_collection')
                         ->addStoreFilter(Mage::app()->getStore())
                         ->addFieldToFilter('status', 1);
        $contacts->setOrder('contact_status', 'asc');
        $this->setContacts($contacts);
    }

    /**
     * prepare the layout
     *
     * @access protected
     * @return Julfiker_Contact_Block_Contact_List
     * @author Ultimate Module Creator
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock(
            'page/html_pager',
            'julfiker_contact.contact.html.pager'
        )
        ->setCollection($this->getContacts());
        $this->setChild('pager', $pager);
        $this->getContacts()->load();
        return $this;
    }

    /**
     * get the pager html
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
