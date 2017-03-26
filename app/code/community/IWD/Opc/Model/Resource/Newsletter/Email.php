<?php

class IWD_Opc_Model_Resource_Newsletter_Email extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('opc/newsletter_email', 'entity_id');
    }
}