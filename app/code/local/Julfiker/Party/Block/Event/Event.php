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
 * Event create block
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author Julfiker
 */
class Julfiker_Party_Block_Event_Event extends Mage_Core_Block_Template
{

    public function getSavingAction() {
        return "/party/event/save";
    }
}
