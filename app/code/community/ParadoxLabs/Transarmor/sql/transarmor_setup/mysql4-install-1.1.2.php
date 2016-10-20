<?php
/**
 * First Data TransArmor integration - Installation script.
 *
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 *
 * Having a problem with the plugin?
 * Not sure what something means?
 * Need custom development?
 * Give us a call!
 *
 * @category	ParadoxLabs
 * @package		ParadoxLabs_Transarmor
 * @author		Ryan Hoerr <ryan@paradoxlabs.com>
 */

$table = $this->getTable('transarmor/card');

$this->startSetup();

$this->run("CREATE TABLE IF NOT EXISTS {$table} (
	id int auto_increment primary key,
	trans_id varchar(255),
	trans_expires int,
	customer_id int,
	type varchar(255),
	last4 varchar(4),
	notify int,
	stored TINYINT NOT NULL DEFAULT '1',
	firstname varchar(32),
	lastname varchar(32),
	additional_info text
);");

Mage::log( 'First Data - Payment Module Installed', null, 'firstdata.log' );

$this->endSetup();
