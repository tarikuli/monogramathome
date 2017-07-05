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
 * Contact list block
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author Julfiker
 */
class Julfiker_Party_Block_Contact_List extends Mage_Core_Block_Template
{
    /**
     * initialize
     *
     * @access public
     * @author Julfiker
     */
    public function _construct()
    {
        parent::_construct();
        $contacts = Mage::getResourceModel('julfiker_party/contact_collection')
                         ->addFieldToFilter('website_id', Mage::app()->getStore()->getWebsiteId());
        $contacts->setOrder('first_name', 'asc');
        $this->setContacts($contacts);
    }

    /**
     * prepare the layout
     *
     * @access protected
     * @return Julfiker_Party_Block_Contact_List
     * @author Julfiker
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock(
            'page/html_pager',
            'julfiker_party.contact.html.pager'
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
     * @author Julfiker
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
