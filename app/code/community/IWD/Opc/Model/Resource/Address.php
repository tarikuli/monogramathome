<?php

class IWD_Opc_Model_Resource_Address extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('opc/address', 'entity_id');
    }
}