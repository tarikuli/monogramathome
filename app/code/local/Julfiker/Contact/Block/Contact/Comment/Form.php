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
 * Contact comment form block
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author Ultimate Module Creator
 */
class Julfiker_Contact_Block_Contact_Comment_Form extends Mage_Core_Block_Template
{
    /**
     * initialize
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        $customerSession = Mage::getSingleton('customer/session');
        parent::__construct();
        $data =  Mage::getSingleton('customer/session')->getContactCommentFormData(true);
        $data = new Varien_Object($data);
        // add logged in customer name as nickname
        if (!$data->getName()) {
            $customer = $customerSession->getCustomer();
            if ($customer && $customer->getId()) {
                $data->setName($customer->getFirstname());
                $data->setEmail($customer->getEmail());
            }
        }
        $this->setAllowWriteCommentFlag(
            $customerSession->isLoggedIn() ||
            Mage::getStoreConfigFlag('julfiker_contact/contact/allow_guest_comment')
        );
        if (!$this->getAllowWriteCommentFlag()) {
            $this->setLoginLink(
                Mage::getUrl(
                    'customer/account/login/',
                    array(
                        Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME => Mage::helper('core')->urlEncode(
                            Mage::getUrl('*/*/*', array('_current' => true)) .
                            '#comment-form'
                        )
                    )
                )
            );
        }
        $this->setCommentData($data);
    }

    /**
     * get current contact
     *
     * @access public
     * @return Julfiker_Contact_Model_Contact
     * @author Ultimate Module Creator
     */
    public function getContact()
    {
        return Mage::registry('current_contact');
    }

    /**
     * get form action
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getAction()
    {
        return Mage::getUrl(
            'julfiker_contact/contact/commentpost',
            array('id' => $this->getContact()->getId())
        );
    }
}
