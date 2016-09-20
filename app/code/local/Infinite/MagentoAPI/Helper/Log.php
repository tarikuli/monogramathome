<?php

class Infinite_MagentoAPI_Helper_Log extends Infinite_MagentoAPI_Helper_Data
{
	const LOG_FILENAME = 'infinite_api.log';

	public function error($message)
	{
		$message = Mage::log("ERROR: " . $message, null, self::LOG_FILENAME);
	}

	public function info($message)
	{
		$message = Mage::log($message, null, self::LOG_FILENAME);
	}
}