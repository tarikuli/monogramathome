<?php

$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$installer->getTable('opc/newsletter_email')};
CREATE TABLE {$installer->getTable('opc/newsletter_email')} (
    `entity_id` int(11) unsigned NOT NULL auto_increment,
    `newsletter_id` int(11) default 0,
    `customer_id` int(11) default 0,
    PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup(); 