<?php 

class Infinite_CustomerLogin_Helper_Customer_Data extends Mage_Customer_Helper_Data
{
	public function getAccountUrl()
    {
    	$accountUrl = $this->_getUrl('customer/account');

    	$redirectEnable = Mage::getStoreConfig('shop_redirect/redirect_settings/enable');
        $mainWebsiteId = Mage::getStoreConfig('shop_redirect/redirect_settings/main_website');
        if($redirectEnable && Mage::app()->getWebsite()->getId() == $mainWebsiteId)
        {
            $currentBaseURL = Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

            $redirectWebsiteId = Mage::getStoreConfig('shop_redirect/redirect_settings/redirect_website');

            $shopWebsiteObject = Mage::getModel('core/website')->load($redirectWebsiteId);
            $shopStoreId = $shopWebsiteObject->getDefaultGroup()->getDefaultStoreId();
            $shopStoreObject = Mage::getModel('core/store')->load($shopStoreId);
            $shopBaseURL = $shopStoreObject->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

            $accountUrl = str_replace($currentBaseURL, $shopBaseURL, $accountUrl);
        }

        return $accountUrl;
    }

    public function getRegisterUrl()
    {
    	$registerUrl = $this->_getUrl('customer/account/create');

    	$redirectEnable = Mage::getStoreConfig('shop_redirect/redirect_settings/enable');
        $mainWebsiteId = Mage::getStoreConfig('shop_redirect/redirect_settings/main_website');
        if($redirectEnable && Mage::app()->getWebsite()->getId() == $mainWebsiteId)
        {
            $currentBaseURL = Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

            $redirectWebsiteId = Mage::getStoreConfig('shop_redirect/redirect_settings/redirect_website');

            $shopWebsiteObject = Mage::getModel('core/website')->load($redirectWebsiteId);
            $shopStoreId = $shopWebsiteObject->getDefaultGroup()->getDefaultStoreId();
            $shopStoreObject = Mage::getModel('core/store')->load($shopStoreId);
            $shopBaseURL = $shopStoreObject->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

            $registerUrl = str_replace($currentBaseURL, $shopBaseURL, $registerUrl);
        }

        return $registerUrl;
    }

    /*
    public function getLoginUrl()
    {
        $loginUrl = $this->_getUrl(self::ROUTE_ACCOUNT_LOGIN, $this->getLoginUrlParams());

    	$redirectEnable = Mage::getStoreConfig('shop_redirect/redirect_settings/enable');
        $mainWebsiteId = Mage::getStoreConfig('shop_redirect/redirect_settings/main_website');
        if($redirectEnable && Mage::app()->getWebsite()->getId() == $mainWebsiteId)
        {
            $currentBaseURL = Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

            $redirectWebsiteId = Mage::getStoreConfig('shop_redirect/redirect_settings/redirect_website');

            $shopWebsiteObject = Mage::getModel('core/website')->load($redirectWebsiteId);
            $shopStoreId = $shopWebsiteObject->getDefaultGroup()->getDefaultStoreId();
            $shopStoreObject = Mage::getModel('core/store')->load($shopStoreId);
            $shopBaseURL = $shopStoreObject->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

            $loginUrl = str_replace($currentBaseURL, $shopBaseURL, $loginUrl);
        }

        return $loginUrl;
    }
    */
}

?>