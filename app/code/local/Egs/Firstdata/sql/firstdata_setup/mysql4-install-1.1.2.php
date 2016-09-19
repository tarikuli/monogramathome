<?php
$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$installer->run("
                CREATE TABLE IF NOT EXISTS `firstdatagge4` (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`order_id` bigint(20) NOT NULL,
		`transaction_type` varchar(20) NOT NULL,
		`transaction_tag` varchar(255) NOT NULL,
		`authorization_num` varchar(100) NOT NULL,
                `bank_response_code` varchar(255) NOT NULL,
                `bank_response_msg` varchar(255) NOT NULL,
                `client_ip` varchar(100) NOT NULL,
                `ctr` longtext NOT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
");

$installer->endSetup();
