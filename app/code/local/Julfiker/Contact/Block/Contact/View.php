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
 * Contact view block
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author      Ultimate Module Creator
 */
class Julfiker_Contact_Block_Contact_View extends Mage_Core_Block_Template
{
    /**
     * get the current contact
     *
     * @access public
     * @return mixed (Julfiker_Contact_Model_Contact|null)
     * @author Ultimate Module Creator
     */
    public function getCurrentContact()
    {
        return Mage::registry('current_contact');
    }
}
