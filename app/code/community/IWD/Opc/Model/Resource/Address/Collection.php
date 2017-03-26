<?php

class IWD_Opc_Model_Resource_Address_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('opc/address');
    }
}

?>