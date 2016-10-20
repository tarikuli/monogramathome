<?php

class Infinite_MagentoAPI_Helper_Data extends Mage_Core_Helper_Abstract
{
	const ENCRYPT_DECRYPT_KEY = 'InfiniteMagentoAPI';

	public function encrypt($data)
	{
	    return base64_encode(mcrypt_encrypt(
    		MCRYPT_RIJNDAEL_128, 
    		self::ENCRYPT_DECRYPT_KEY, 
    		$data, 
    		MCRYPT_MODE_CBC, 
    		"\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0"
	    ));
	}

	public function decrypt($data)
	{
	    $decode = base64_decode($data);
	    return mcrypt_decrypt(
    		MCRYPT_RIJNDAEL_128, 
    		self::ENCRYPT_DECRYPT_KEY, 
    		$decode, 
    		MCRYPT_MODE_CBC, 
    		"\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0"
    	);
	}
}