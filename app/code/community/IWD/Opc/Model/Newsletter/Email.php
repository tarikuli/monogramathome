<?php

class IWD_Opc_Model_Newsletter_Email extends Mage_Core_Model_Abstract 
{
    public function _construct() 
    {
        parent::_construct();
        $this->_init('opc/newsletter_email');
    }
}