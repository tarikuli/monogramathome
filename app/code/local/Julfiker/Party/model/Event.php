<?php

/**
 * Created by PhpStorm.
 * User: julfiker
 */
class Julfiker_Contact_Model_Event extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('party/event');
    }
}