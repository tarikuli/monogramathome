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
 * Party_success_promotion resource model
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Model_Resource_Partysuccesspromotion extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * constructor
     *
     * @access public
     * @author Julfiker
     */
    public function _construct()
    {
        $this->_init('julfiker_party/partysuccesspromotion', 'entity_id');
    }
}
