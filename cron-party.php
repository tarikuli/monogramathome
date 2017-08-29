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
	$hostAwardAmount = $_eventHelper->getHostAwardAmount();
	$discountedItems = $_eventHelper->getMaxDiscounteditems();

    if ($earningAmount) {
        $party_success = Mage::getModel('julfiker_party/partysuccesspromotion');
        $party_success->setEventId($event->getId());
        $party_success->setCustomerId($event->getHost());
        $party_success->setHostReward($hostAwardAmount);
        $party_success->setRewardItemQty($discountedItems);
        $party_success->setCashEarning($earningAmount);
        $party_success->setRetailSales($salesAmount);
        $party_success->setPromoCode(generatePromoCode($event->getId(), $event->getHost()));
        $party_success->setItemPromoCode(generatePromoCode($event->getId(), $event->getHost()));
        $party_success->setStatus(0);
        $party_success->save();
        if ($party_success->getId()) {
            mage::log("promotion code generated for party id " . $event->getId());
        }
    }
    $event->setIsOver(1);
    $event->save();
}

function generatePromoCode($eventId, $host_id) {
    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $res = "";
    for ($i = 0; $i < 6; $i++) {
        $res .= $chars[mt_rand(0, strlen($chars)-1)];
    }
    return $res = $eventId.$res.$host_id;
}


function createGiftCard() {
	//Gift code data stucture.
	$data = array (
    "gift_code" => "Event_02_04",
    "balance" => 200,
    "currency" => "USD",
    "giftcard_template_id" => 1,
    "giftcard_template_image" => "default.png",
    "giftvoucher_status" => 2,
    "expired_at" => "2017-05-31",
    "store_id" => "0",
    "giftvoucher_comments" => "Event target one coupon code",
    "description" => 'testing',
    "customer_name" => 'customer name',
    "customer_email" => 'email',
    "recipient_name" => 'recipient name',
    "recipient_email" => 'email',
    "recipient_address" => 'address',
    "message" => "message here",
    "status" => 2,
    "comments" => "Event target one coupon code",
    "amount" => 200,
    "conditions" => array
	(
		"1" => array
		(
			"type" => "salesrule/rule_condition_combine",
                    "aggregator" => "all",
                    "value" => 1,
                    "new_child" =>""
                )

        ),

    "actions" => array
	(
		"1" => array
		(
			"type" => "salesrule/rule_condition_product_combine",
                    "aggregator" => "all",
                    "value" => "1",
                    "new_child" => ""
                )

        ),

    "extra_content" => "auto crate for event host"
   );
}