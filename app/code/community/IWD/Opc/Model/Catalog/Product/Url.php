<?php 

class IWD_Opc_Model_Catalog_Product_Url extends Mage_Catalog_Model_Product_Url
{
	public function getUrl(Mage_Catalog_Model_Product $product, $params = array())
    {
        $url = $product->getData('url');
        if (!empty($url)) {
            return $url;
        }

        $requestPath = $product->getData('request_path');
        if (empty($requestPath)) {
            $requestPath = $this->_getRequestPath($product, $this->_getCategoryIdForUrl($product, $params));
            $product->setRequestPath($requestPath);
        }

        if (isset($params['_store'])) {
            $storeId = $this->_getStoreId($params['_store']);
        } else {
            $storeId = $product->getStoreId();
        }

        if ($storeId != $this->_getStoreId()) {
            $params['_store_to_url'] = true;
        }

        // reset cached URL instance GET query params
        if (!isset($params['_query'])) {
            $params['_query'] = array();
        }

        $this->getUrlInstance()->setStore($storeId);
        $productUrl = $this->_getProductUrl($product, $requestPath, $params);

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

            $productUrl = str_replace($currentBaseURL, $shopBaseURL, $productUrl);
        }

        $product->setData('url', $productUrl);
        return $product->getData('url');
    }
}

?>