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
 * Contact customer comments list
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author      Ultimate Module Creator
 */
class Julfiker_Contact_Block_Contact_Customer_Comment_View extends Mage_Customer_Block_Account_Dashboard
{
    /**
     * get current comment
     *
     * @access public
     * @return Julfiker_Contact_Model_Contact_Comment
     * @author Ultimate Module Creator
     */
    public function getComment()
    {
        return Mage::registry('current_comment');
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
}
