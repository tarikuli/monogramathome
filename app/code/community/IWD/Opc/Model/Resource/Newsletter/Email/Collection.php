<?php

class IWD_Opc_Model_Resource_Newsletter_Email_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('opc/newsletter_email');
    }
}

?>