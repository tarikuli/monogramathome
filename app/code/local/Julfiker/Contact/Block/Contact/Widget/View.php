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
 * Contact widget block
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author      Ultimate Module Creator
 */
class Julfiker_Contact_Block_Contact_Widget_View extends Mage_Core_Block_Template implements
    Mage_Widget_Block_Interface
{
    protected $_htmlTemplate = 'julfiker_contact/contact/widget/view.phtml';

    /**
     * Prepare a for widget
     *
     * @access protected
     * @return Julfiker_Contact_Block_Contact_Widget_View
     * @author Ultimate Module Creator
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $contactId = $this->getData('contact_id');
        if ($contactId) {
            $contact = Mage::getModel('julfiker_contact/contact')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($contactId);
            if ($contact->getStatus()) {
                $this->setCurrentContact($contact);
                $this->setTemplate($this->_htmlTemplate);
            }
        }
        return $this;
    }
}
