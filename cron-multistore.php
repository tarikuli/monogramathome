<?php
/**
 * Created by PhpStorm.
 * User: julfiker
 * Date: 9/20/16
 * Time: 12:15 AM
 */

switch($_GET['clean']) {
	case 'log':
		clean_log_tables();
		break;
	case 'var':
		clean_var_directory();
		break;
}

function clean_log_tables() {
	$xml = simplexml_load_file('./app/etc/local.xml', NULL, LIBXML_NOCDATA);

	if(is_object($xml)) {
		$db['host'] = $xml->global->resources->default_setup->connection->host;
		$db['name'] = $xml->global->resources->default_setup->connection->dbname;
		$db['user'] = $xml->global->resources->default_setup->connection->username;
		$db['pass'] = $xml->global->resources->default_setup->connection->password;
		$db['pref'] = $xml->global->resources->db->table_prefix;

		$tables = array(
				'ambassador_queues',
				'customer_entity',
				'customer_entity_datetime',
				'customer_entity_decimal',
				'customer_entity_int',
				'customer_entity_text',
				'customer_entity_varchar',
				'customer_address_entity',
				'customer_address_entity_datetime',
				'customer_address_entity_decimal',
				'customer_address_entity_int',
				'customer_address_entity_text',
				'customer_address_entity_varchar',
				'sales_flat_quote',
				'sales_flat_quote_address',
				'sales_flat_quote_address_item',
				'sales_flat_quote_item',
				'sales_flat_quote_item_option',
				'sales_flat_quote_payment',
				'sales_flat_quote_shipping_rate',
				'sales_flat_order',
				'sales_flat_order_address',
				'sales_flat_order_grid',
				'sales_flat_order_item',
				'sales_flat_order_payment',
				'sales_flat_order_status_history',
				'sales_flat_invoice',
				'sales_flat_invoice_comment',
				'sales_flat_invoice_grid',
				'sales_flat_invoice_item',
				'sales_flat_shipment',
				'sales_flat_shipment_comment',
				'sales_flat_shipment_grid',
				'sales_flat_shipment_item',
				'sales_flat_shipment_track',
				'sales_order_tax',
				'sales_order_tax_item',
				'sales_flat_creditmemo',
				'sales_flat_creditmemo_comment',
				'sales_flat_creditmemo_grid',
				'sales_flat_creditmemo_item',
				'coupon_aggregated',
				'coupon_aggregated_order',
				'coupon_aggregated_updated',
				'report_viewed_product_aggregated_daily',
				'report_viewed_product_aggregated_monthly',
				'report_viewed_product_aggregated_yearly',
				'sales_bestsellers_aggregated_daily',
				'sales_bestsellers_aggregated_monthly',
				'sales_bestsellers_aggregated_yearly',
				'sales_invoiced_aggregated',
				'sales_invoiced_aggregated_order',
				'sales_order_aggregated_created',
				'sales_order_aggregated_updated',
				'sales_refunded_aggregated',
				'sales_refunded_aggregated_order',
				'sales_shipping_aggregated',
				'sales_shipping_aggregated_order',
				'tax_order_aggregated_created',
				'tax_order_aggregated_updated',
				'log_customer',
				'log_quote',
				'log_summary',
				'log_summary_type',
				'log_url',
				'log_url_info',
				'log_visitor',
				'log_visitor_info',
				'log_visitor_online',
				'catalogsearch_query',
				'index_event',
				'index_process_event',
				'report_event',
				'report_viewed_product_index',
				'sendfriend_log',
				'tag',
				'tag_relation',
				'tag_summary',
				'wishlist'
		);

		mysql_connect($db['host'], $db['user'], $db['pass']) or die(mysql_error());
		mysql_select_db($db['name']) or die(mysql_error());

		@mysql_query('SET foreign_key_checks = 0');
		foreach($tables as $table) {
			@mysql_query('TRUNCATE `'.$db['pref'].$table.'`');
		}
		@mysql_query('SET foreign_key_checks = 1');
	} else {
		exit('Unable to load local.xml file');
	}
}

function clean_var_directory() {
	$dirs = array(
			'downloader/.cache/',
			'downloader/pearlib/cache/*',
			'downloader/pearlib/download/*',
			'var/cache/',
			'var/locks/',
			'var/log/',
			'var/report/',
			'var/session/',
			'var/tmp/'
	);

	foreach($dirs as $dir) {
		exec('rm -rf '.$dir);
	}
}


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

//To create store based on queue
$runUrl = $baseUrl."run";
echo "1. Creating multi store based on domain queue ... <br />";
$response = call($runUrl);
echo "status = ".$response['status'].", ".$response['message'];

//Configure multi store
$runUrl = $baseUrl."config";
echo "<br /><br /> 2. Configuring created multistore ... <br />";
$response = call($runUrl);
echo "status = ".$response['status'].", ".$response['message'];

//Indexing catalog
$runUrl = $baseUrl."htaccess";
echo "<br/><br /> 3. Updating htaccess with multi domain ... <br />";
$response = call($runUrl);
echo "status = ".$response['status'].", ".$response['message'];

//Clearing cache
$runUrl = $baseUrl."clearcache";
echo "<br/><br /> 4. Clearing all cache ... <br />";
$response = call($runUrl);
echo "status = ".$response['status'].", ".$response['message'];

//Assign all products to other store
$runUrl = $baseUrl."products";
echo "<br/> <br /> 5. Assigning products to other store ... <br />";
$response = call($runUrl);
echo "status = ".$response['status'].", ".$response['message'];

//Indexing catalog
//$runUrl = $baseUrl."indexing";
//echo "<br/> <br /> 6. Indexing all catalog ... <br />";
//$response = call($runUrl);
//echo "status = ".$response['status'].", ".$response['message'];
