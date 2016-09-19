<?php
/**
 * Created by PhpStorm.
 * User: julfiker
 * Date: 9/20/16
 * Time: 12:15 AM
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);
// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

require 'app/bootstrap.php';
require 'app/Mage.php';
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

function call($url, $data = array()) {
    try {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);
        curl_close($handle);
        addLog("url :".$url.", response :".$response);
        $responseArray = json_decode($response, true);
        return $responseArray;
    }
    catch(\Exception $e) {
        addLog($e->getMessage());
    }
}

function addLog($message) {
    Mage::log($message, null, 'multi-store-setup.log');
}
$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)."contact".DS."queue".DS;





