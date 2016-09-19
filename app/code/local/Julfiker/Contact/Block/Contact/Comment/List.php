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
 * Contact comment list block
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author Ultimate Module Creator
 */
class Julfiker_Contact_Block_Contact_Comment_List extends Mage_Core_Block_Template
{
    /**
     * initialize
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $contact = $this->getContact();
        $comments = Mage::getResourceModel('julfiker_contact/contact_comment_collection')
            ->addFieldToFilter('contact_id', $contact->getId())
            ->addStoreFilter(Mage::app()->getStore())
             ->addFieldToFilter('status', 1);
        $comments->setOrder('created_at', 'asc');
        $this->setComments($comments);
    }

    /**
     * prepare the layout
     *
     * @access protected
     * @return Julfiker_Contact_Block_Contact_Comment_List
     * @author Ultimate Module Creator
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock(
            'page/html_pager',
            'julfiker_contact.contact.html.pager'
        )
        ->setCollection($this->getComments());
        $this->setChild('pager', $pager);
        $this->getComments()->load();
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
    /**
     * get the current contact
     *
     * @access protected
     * @return Julfiker_Contact_Model_Contact
     * @author Ultimate Module Creator
     */
    public function getContact()
    {
        return Mage::registry('current_contact');
    }
}
