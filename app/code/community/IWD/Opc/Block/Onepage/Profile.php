<?php

class IWD_Opc_Block_Onepage_Profile extends Mage_Checkout_Block_Onepage_Billing
{
	protected function _getSession(){
		return Mage::getSingleton('checkout/session');
	}

	public function getFormData($field)
	{
		$ambassadorProfileInfo = Mage::getSingleton('core/session')->getAmbassadorProfileInfo();
		if(isset($ambassadorProfileInfo) && isset($ambassadorProfileInfo[$field]))
			return $ambassadorProfileInfo[$field];
		return "";
	}
}
