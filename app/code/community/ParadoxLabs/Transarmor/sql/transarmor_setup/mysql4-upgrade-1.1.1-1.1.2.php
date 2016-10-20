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

$this->startSetup();

$table = $this->getTable('transarmor/card');

$this->run("ALTER TABLE {$table} ADD stored TINYINT NOT NULL DEFAULT '1' AFTER notify;");

$this->endSetup();

Mage::log( 'First Data - Updated to 1.1.2', null, 'firstdata.log' );
