<?php

class IWD_Opc_Block_Onepage_Starterkit extends Mage_Core_Block_Template
{
	protected function _getSession()
	{
		return Mage::getSingleton('checkout/session');
	}

	protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

	public function getStarterKitCollection()
	{
		$starterKitCollection = Mage::getModel('catalog/product')->getCollection()
    		->addAttributeToSelect("*")
    		->addAttributeToFilter('is_starter_kit', 1);

    	return $starterKitCollection;
	}

	public function getCurrentProductId()
	{
		$quote = $this->_getCart()->getQuote();
		if ($quote->hasItems() && !$quote->getHasError()) {
			foreach ($quote->getAllItems() as $item) {
			    return $item->getProduct()->getId();
			    //break;
			}
		}
		
		return "";
	}

	public function getWebsiteName()
	{
		$websiteName = Mage::getSingleton('core/session')->getAmbassadorWebsiteName();
		if(isset($websiteName))
			return $websiteName;
		
		return "";
	}
}
