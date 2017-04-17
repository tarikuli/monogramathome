<?php

/**
 * Created by PhpStorm.
 * User: julfiker
 */
class Julfiker_Contact_Model_Resource_Event extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('party/event', 'id');
    }
}