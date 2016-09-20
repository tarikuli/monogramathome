<?php
class Egs_Firstdata_Block_Form_Firstdata extends Mage_Payment_Block_Form_Ccsave
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('firstdata/ccsave.phtml');
    }
}