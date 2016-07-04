<?php

$installer = $this;

$installer->startSetup();
 
$installer->run("
	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/template')}`;
	 CREATE TABLE `{$this->getTable('optionextended/template')}` (
		`template_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` varchar(255) NOT NULL DEFAULT '',
		`code` varchar( 64 ) NOT NULL,
		`is_active` tinyint(1) NOT NULL default 1,		
		UNIQUE `OPTIONEXTENDED_TEMPLATE_CODE` (`code`)
	 )ENGINE=InnoDB default CHARSET=utf8;
	 	 
	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/template_option')}`;
	 CREATE TABLE `{$this->getTable('optionextended/template_option')}` (
		`option_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`template_id` int unsigned NOT NULL,
    `code` varchar( 64 ) NOT NULL,		
		`row_id` smallint unsigned DEFAULT NULL,
    `type` varchar(50) NOT NULL DEFAULT '',
    `is_require` tinyint(1) NOT NULL DEFAULT '1',
    `sku` varchar(64) NOT NULL DEFAULT '',
    `max_characters` int(10) unsigned DEFAULT NULL,
    `file_extension` varchar(50) DEFAULT NULL,
    `image_size_x` smallint(5) unsigned NOT NULL,
    `image_size_y` smallint(5) unsigned NOT NULL,
    `sort_order` int(10) unsigned NOT NULL DEFAULT '0',		
		`layout` enum('above','below','before','swap','picker','pickerswap','grid','list') NOT NULL default 'above',
		`popup` tinyint(1) NOT NULL default 0,
		`selected_by_default` varchar(255) NOT NULL default '',		
		KEY `OPTIONEXTENDED_TEMPLATE_OPTION_TEMPLATE` (`template_id`),
		UNIQUE `OPTIONEXTENDED_TEMPLATE_OPTION_CODE` (`code`),
		CONSTRAINT `FK_OPTIONEXTENDED_TEMPLATE_OPTION_TEMPLATE` FOREIGN KEY (`template_id`) REFERENCES `{$this->getTable('optionextended/template')}` (`template_id`) ON DELETE CASCADE ON UPDATE CASCADE
	 )ENGINE=InnoDB default CHARSET=utf8;

	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/template_option_title')}`;
	 CREATE TABLE `{$this->getTable('optionextended/template_option_title')}` (
		`option_title_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`option_id` int unsigned NOT NULL,
		`store_id` smallint(5) unsigned NOT NULL,
    `title` varchar(255) NOT NULL DEFAULT '',
		KEY `OPTIONEXTENDED_TEMPLATE_OPTION_TITLE_OPTION` (`option_id`),
		KEY `OPTIONEXTENDED_TEMPLATE_OPTION_TITLE_STORE` (`store_id`),
		CONSTRAINT `OPTIONEXTENDED_TEMPLATE_OPTION_TITLE_OPTION` FOREIGN KEY (`option_id`) REFERENCES `{$this->getTable('optionextended/template_option')}` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `FK_OPTIONEXTENDED_TEMPLATE_OPTION_TITLE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY `IDX_OPTIONEXTENDED_TEMPLATE_OPTION_TITLE_SI_OTOI` (`store_id`, `option_id`)
	 )ENGINE=InnoDB default CHARSET=utf8;
	 
	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/template_option_price')}`;
	 CREATE TABLE `{$this->getTable('optionextended/template_option_price')}` (
		`option_price_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`option_id` int unsigned NOT NULL,
		`store_id` smallint(5) unsigned NOT NULL,
    `price` decimal(12,4) NOT NULL DEFAULT '0.0000',
    `price_type` enum('fixed','percent') NOT NULL DEFAULT 'fixed',
		KEY `OPTIONEXTENDED_TEMPLATE_OPTION_PRICE_OPTION` (`option_id`),
		KEY `OPTIONEXTENDED_TEMPLATE_OPTION_PRICE_STORE` (`store_id`),
		CONSTRAINT `FK_OPTIONEXTENDED_TEMPLATE_OPTION_PRICE_OPTION` FOREIGN KEY (`option_id`) REFERENCES `{$this->getTable('optionextended/template_option')}` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `FK_OPTIONEXTENDED_TEMPLATE_OPTION_PRICE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      KEY `IDX_OPTIONEXTENDED_TEMPLATE_OPTION_PRICE_SI_OTOI` (`store_id`, `option_id`)
	 )ENGINE=InnoDB default CHARSET=utf8;
	 
	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/template_option_note')}`;
	 CREATE TABLE `{$this->getTable('optionextended/template_option_note')}` (
		`option_note_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`option_id` int unsigned NOT NULL,
		`store_id` smallint(5) unsigned NOT NULL,
		`note` text NOT NULL default '',
		KEY `OPTIONEXTENDED_TEMPLATE_OPTION_NOTE_OPTION` (`option_id`),
		KEY `OPTIONEXTENDED_TEMPLATE_OPTION_NOTE_STORE` (`store_id`),
		CONSTRAINT `FK_OPTIONEXTENDED_TEMPLATE_OPTION_NOTE_OPTION` FOREIGN KEY (`option_id`) REFERENCES `{$this->getTable('optionextended/template_option')}` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `FK_OPTIONEXTENDED_TEMPLATE_OPTION_NOTE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      KEY `IDX_OPTIONEXTENDED_TEMPLATE_OPTION_NOTE_SI_OTOI` (`store_id`, `option_id`)
	 )ENGINE=InnoDB default CHARSET=utf8;

	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/template_value')}`;
	 CREATE TABLE `{$this->getTable('optionextended/template_value')}` (
		`value_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`option_id` int unsigned NOT NULL,
		`row_id` smallint unsigned DEFAULT NULL,
    `sku` varchar(64) NOT NULL DEFAULT '',
    `sort_order` int(10) unsigned NOT NULL DEFAULT '0',		
		`children` text NOT NULL default '',
		`image` varchar(255) NOT NULL default '',
		KEY `OPTIONEXTENDED_TEMPLATE_VALUE_OPTION` (`option_id`),
		CONSTRAINT `FK_OPTIONEXTENDED_TEMPLATE_VALUE_OPTION` FOREIGN KEY (`option_id`) REFERENCES `{$this->getTable('optionextended/template_option')}` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE
	 )ENGINE=InnoDB default CHARSET=utf8;

	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/template_value_title')}`;
	 CREATE TABLE `{$this->getTable('optionextended/template_value_title')}` (
		`value_description_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`value_id` int unsigned NOT NULL,
		`store_id` smallint(5) unsigned NOT NULL,
    `title` varchar(255) NOT NULL DEFAULT '',
		KEY `OPTIONEXTENDED_TEMPLATE_VALUE_TITLE_VALUE` (`value_id`),
		KEY `OPTIONEXTENDED_TEMPLATE_VALUE_TITLE_STORE` (`store_id`),
		CONSTRAINT `FK_OPTIONEXTENDED_TEMPLATE_VALUE_TITLE_VALUE` FOREIGN KEY (`value_id`) REFERENCES `{$this->getTable('optionextended/template_value')}` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `FK_OPTIONEXTENDED_TEMPLATE_VALUE_TITLE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      KEY `IDX_OPTIONEXTENDED_TEMPLATE_VALUE_TITLE_SI_OTVI` (`store_id`, `value_id`)
	 )ENGINE=InnoDB default CHARSET=utf8;

	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/template_value_price')}`;
	 CREATE TABLE `{$this->getTable('optionextended/template_value_price')}` (
		`value_description_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`value_id` int unsigned NOT NULL,
		`store_id` smallint(5) unsigned NOT NULL,
    `price` decimal(12,4) NOT NULL DEFAULT '0.0000',
    `price_type` enum('fixed','percent') NOT NULL DEFAULT 'fixed',
		KEY `OPTIONEXTENDED_TEMPLATE_VALUE_PRICE_VALUE` (`value_id`),
		KEY `OPTIONEXTENDED_TEMPLATE_VALUE_PRICE_STORE` (`store_id`),
		CONSTRAINT `FK_OPTIONEXTENDED_TEMPLATE_VALUE_PRICE_VALUE` FOREIGN KEY (`value_id`) REFERENCES `{$this->getTable('optionextended/template_value')}` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `FK_OPTIONEXTENDED_TEMPLATE_VALUE_PRICE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      KEY `IDX_OPTIONEXTENDED_TEMPLATE_VALUE_PRICE_SI_OTVI` (`store_id`, `value_id`)
	 )ENGINE=InnoDB default CHARSET=utf8;

	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/template_value_description')}`;
	 CREATE TABLE `{$this->getTable('optionextended/template_value_description')}` (
		`value_description_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`value_id` int unsigned NOT NULL,
		`store_id` smallint(5) unsigned NOT NULL,
		`description` text NOT NULL default '',
		KEY `OPTIONEXTENDED_TEMPLATE_VALUE_DESCRIPTION_VALUE` (`value_id`),
		KEY `OPTIONEXTENDED_TEMPLATE_VALUE_DESCRIPTION_STORE` (`store_id`),
		CONSTRAINT `FK_OPTIONEXTENDED_TEMPLATE_VALUE_DESCRIPTION_VALUE` FOREIGN KEY (`value_id`) REFERENCES `{$this->getTable('optionextended/template_value')}` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `FK_OPTIONEXTENDED_TEMPLATE_VALUE_DESCRIPTION_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      KEY `IDX_OPTIONEXTENDED_TEMPLATE_VALUE_DESCRIPTION_SI_OTVI` (`store_id`, `value_id`)
	 )ENGINE=InnoDB default CHARSET=utf8;


	 DROP TABLE IF EXISTS `{$this->getTable('optionextended/product_template')}`;
	 CREATE TABLE `{$this->getTable('optionextended/product_template')}` (
    `product_id` int(10) unsigned NOT NULL DEFAULT '0',	 
		`template_id` int unsigned NOT NULL,
   UNIQUE KEY `UNQ_PRODUCT_TEMPLATE` (`product_id`,`template_id`),
   KEY `OPTIONEXTENDED_PRODUCT_TEMPLATE_PRODUCT` (`product_id`),       
   KEY `OPTIONEXTENDED_PRODUCT_TEMPLATE_TEMPLATE` (`template_id`),    		
	 CONSTRAINT `OPTIONEXTENDED_PRODUCT_TEMPLATE_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	 CONSTRAINT `OPTIONEXTENDED_PRODUCT_TEMPLATE_TEMPLATE` FOREIGN KEY (`template_id`) REFERENCES `{$this->getTable('optionextended/template')}` (`template_id`) ON DELETE CASCADE ON UPDATE CASCADE   	    
	 )ENGINE=InnoDB default CHARSET=utf8;


");

$installer->endSetup(); 

