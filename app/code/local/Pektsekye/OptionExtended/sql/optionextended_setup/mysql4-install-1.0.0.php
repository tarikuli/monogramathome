<?php

$installer = $this;
$installer->startSetup();
		
$installer->run("

	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/option')}`;
	 CREATE TABLE `{$this->getTable('optionextended/option')}` (
		`ox_option_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`option_id` int unsigned NOT NULL,
		`product_id` int unsigned NOT NULL,
		`row_id` smallint unsigned DEFAULT NULL,
		`layout` enum('above','below','before','swap','picker','pickerswap','grid','list') NOT NULL default 'above',
		`popup` tinyint(1) NOT NULL default 0,
		`selected_by_default` varchar(255) NOT NULL default '',		
		UNIQUE `OPTIONEXTENDED_OPTION_OPTION_ID` (`option_id`),
		KEY (`product_id`),
		CONSTRAINT `FK_OPTIONEXTENDED_OPTION_OPTION_ID` FOREIGN KEY (`option_id`) REFERENCES `{$this->getTable('catalog/product_option')}` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE
	 )ENGINE=InnoDB default CHARSET=utf8;

	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/option_note')}`;
	 CREATE TABLE `{$this->getTable('optionextended/option_note')}` (
		`ox_option_note_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`ox_option_id` int unsigned NOT NULL,
		`store_id` smallint(5) unsigned NOT NULL,
		`note` text NOT NULL default '',
		KEY `OPTIONEXTENDED_OPTION_NOTE_OX_OPTION_ID` (`ox_option_id`),
		KEY `OPTIONEXTENDED_OPTION_NOTE_STORE_ID` (`store_id`),
		CONSTRAINT `FK_OPTIONEXTENDED_OPTION_NOTE_OX_OPTION_ID` FOREIGN KEY (`ox_option_id`) REFERENCES `{$this->getTable('optionextended/option')}` (`ox_option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `FK_OPTIONEXTENDED_OPTION_NOTE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      KEY `IDX_OPTIONEXTENDED_OPTION_NOTE_SI_OOI` (`store_id`, `ox_option_id`)
	 )ENGINE=InnoDB default CHARSET=utf8;

	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/value')}`;
	 CREATE TABLE `{$this->getTable('optionextended/value')}` (
		`ox_value_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`option_type_id` int unsigned NOT NULL,
		`product_id` int unsigned NOT NULL,
		`row_id` smallint unsigned DEFAULT NULL,
		`children` varchar(255) NOT NULL default '',
		`image` varchar(255) NOT NULL default '',
		UNIQUE `OPTIONEXTENDED_VALUE_OPTION_TYPE_ID` (`option_type_id`),
		KEY (`product_id`),
		CONSTRAINT `FK_OPTIONEXTENDED_VALUE_OPTION_TYPE_ID` FOREIGN KEY (`option_type_id`) REFERENCES `{$this->getTable('catalog/product_option_type_value')}` (`option_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
	 )ENGINE=InnoDB default CHARSET=utf8;

	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/value_description')}`;
	 CREATE TABLE `{$this->getTable('optionextended/value_description')}` (
		`ox_value_description_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`ox_value_id` int unsigned NOT NULL,
		`store_id` smallint(5) unsigned NOT NULL,
		`description` text NOT NULL default '',
		KEY `OPTIONEXTENDED_VALUE_DESCRIPTION_OX_VALUE_ID` (`ox_value_id`),
		KEY `OPTIONEXTENDED_VALUE_DESCRIPTION_STORE_ID` (`store_id`),
		CONSTRAINT `FK_OPTIONEXTENDED_VALUE_DESCRIPTION_OX_VALUE_ID` FOREIGN KEY (`ox_value_id`) REFERENCES `{$this->getTable('optionextended/value')}` (`ox_value_id`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `FK_OPTIONEXTENDED_VALUE_DESCRIPTION_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      KEY `IDX_OPTIONEXTENDED_VALUE_DESCRIPTION_SI_OVI` (`store_id`, `ox_value_id`)
	 )ENGINE=InnoDB default CHARSET=utf8;

	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/import')}`;
	 CREATE TABLE `{$this->getTable('optionextended/import')}` (
		`id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`product_id` int unsigned NOT NULL default 0,
		`option_title` varchar(255) NOT NULL default '',
		`type` varchar(50) NOT NULL default '',
		`is_require` tinyint(1) NOT NULL default 1,
		`option_sort_order` int unsigned NOT NULL default 0,
		`note` text NOT NULL default '',
		`layout` enum('above','below','before','swap','picker','pickerswap','grid','list') NOT NULL default 'above',
		`popup` tinyint(1) NOT NULL default 0,
		`selected_by_default` varchar(255) NOT NULL default '',		
		`max_characters` int unsigned NOT NULL default 0,
		`file_extension` varchar(50) NOT NULL default '',
		`image_size_x` smallint(5) NOT NULL default 0,
		`image_size_y` smallint(5) NOT NULL default 0,
		`value_title` varchar(255) NOT NULL default '',
		`price` decimal(12,4) NOT NULL default '0.0000',
		`price_type` enum('fixed','percent') NOT NULL default 'fixed',
		`sku` varchar(64) NOT NULL default '',
		`value_sort_order` int unsigned NOT NULL default 0,
		`row_id` int unsigned DEFAULT NULL,
		`children` varchar(255) NOT NULL default '',
		`image` varchar(255) NOT NULL default '',
		`description` text NOT NULL default ''
	 )ENGINE=MyISAM default CHARSET=utf8;

");


	$installer->run("
		INSERT INTO `{$this->getTable('optionextended/option')}`
			SELECT 
				NULL,
				`option_id`,
				`product_id`,
				NULL,
				'above',
				0,
				''
			FROM `{$this->getTable('catalog/product_option')}`;

		INSERT INTO `{$this->getTable('optionextended/value')}`
			SELECT 
				NULL,
				`option_type_id`,
				`product_id`,
				NULL,
				'',
				''
			FROM `{$this->getTable('catalog/product_option')}` INNER JOIN `{$this->getTable('catalog/product_option_type_value')}` USING (`option_id`);
	");


$prefix = (string)Mage::getConfig()->getTablePrefix();
$imageTable = $prefix . 'optionimages_product_option_type_image';

if ($this->tableExists($imageTable)){
	$installer->run("
		UPDATE `{$this->getTable('optionextended/value')}`
			SET `image`= 
			  (SELECT `image` FROM `{$imageTable}` WHERE `option_type_id` = `{$this->getTable('optionextended/value')}`.`option_type_id` AND `store_id` = 0);
	");
}	


$odOptionTable = $prefix . 'optiondependent_option';
$odValueTable = $prefix . 'optiondependent_value'; 

if ($this->tableExists($odOptionTable) && $this->tableExists($odValueTable)){
	$installer->run("
		UPDATE `{$this->getTable('optionextended/option')}`
			SET `row_id`= 
			  (SELECT `row_id` FROM `{$odOptionTable}` WHERE `option_id` = `{$this->getTable('optionextended/option')}`.`option_id`);

		UPDATE `{$this->getTable('optionextended/value')}`
			SET `row_id`= (SELECT `row_id` FROM `{$odValueTable}` WHERE `option_type_id` = `{$this->getTable('optionextended/value')}`.`option_type_id`),
          `children`= (SELECT `children` FROM `{$odValueTable}` WHERE `option_type_id` = `{$this->getTable('optionextended/value')}`.`option_type_id`);

	");
}




$installer->endSetup(); 


