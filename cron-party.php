<?php
/**
 * Created by PhpStorm.
 * User: julfiker
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);
// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

require 'app/bootstrap.php';
require 'app/Mage.php';
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
/** @var \Julfiker_Party_Helper_Event $_eventHelper */
$_eventHelper = Mage::helper("julfiker_party/event");

function addLog($message) {
	Mage::log($message, null, 'party.log');
}

$now = Mage::getModel('core/date')->timestamp(time());

$events = Mage::getResourceModel('julfiker_party/event_collection')
	->addFieldToFilter('status', 1)
	->addFieldToFilter('is_over', 0)
	->addFieldToFilter(
		'end_at',
	array(
		'lt' => date ("Y-m-d H:i:s", $now)
	)
);


foreach ($events as $event) {
	$amount = $_eventHelper->sumOrders($event->getId());
	$earningAmount = $_eventHelper->getEventEarningAmount();
	$salesAmount = $_eventHelper->getEventSalesAmount();
	$hostAwardAmount = $_eventHelper->getHostAward();
	$discountedItems = $_eventHelper->getHostDiscountItems();
}

