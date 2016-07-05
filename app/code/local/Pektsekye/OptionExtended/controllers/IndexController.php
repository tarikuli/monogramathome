<?php

class Pektsekye_OptionExtended_IndexController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        $this->setUsedModuleName('Pektsekye_OptionExtended');
    }

    public function deniedJsonAction()
    {
        $this->getResponse()->setBody($this->_getDeniedJson());
    }

    protected function _getDeniedJson()
    {
        return Zend_Json::encode(
            array(
                'ajaxExpired'  => 1,
                'ajaxRedirect' => $this->getUrl('adminhtml/index/login')
            )
        );
    }

    protected function _isAllowed()
    {
        return true;
    }

}