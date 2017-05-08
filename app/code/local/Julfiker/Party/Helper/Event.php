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
 * Event helper
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Helper_Event extends Mage_Core_Helper_Abstract
{

    /**
     * get the url to the events list page
     *
     * @access public
     * @return string
     * @author Julfiker
     */
    public function getEventsUrl()
    {
        if ($listKey = Mage::getStoreConfig('julfiker_party/event/url_rewrite_list')) {
            return Mage::getUrl('', array('_direct'=>$listKey));
        }
        return Mage::getUrl('julfiker_party/event/index');
    }

    /**
     * check if breadcrumbs can be used
     *
     * @access public
     * @return bool
     * @author Julfiker
     */
    public function getUseBreadcrumbs()
    {
        return Mage::getStoreConfigFlag('julfiker_party/event/breadcrumbs');
    }
}
