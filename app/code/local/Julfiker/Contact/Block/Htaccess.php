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
 * Contact httaccess block
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author      Ultimate Module Creator
 */
class Julfiker_Contact_Block_Htaccess extends Mage_Core_Block_Template
{

    public function getDomains() {
        $domains = array();
        foreach (Mage::app()->getWebsites() as $website) {
            if ($website->getCode() == "base") continue;
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $domains[] = array(
                        "domain" => $website->getName(),
                        "store" => $store->getCode()
                    );
                }
            }
        }
        return $domains;
    }
}
