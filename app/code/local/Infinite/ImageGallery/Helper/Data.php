<?php

class Infinite_ImageGallery_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getImageDir()
	{
		return Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . $this->getFilePath();
	}

	public function getImageUrl()
	{
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
	}

	public function getFilePath()
	{
		return "infinite" . DS . "images" . DS;
	}
}