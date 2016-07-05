<?php

$installer = $this;

$installer->startSetup();

$installer->run("
  ALTER TABLE `{$this->getTable('optionextended/option')}` CHANGE `layout` `layout` varchar(64) NOT NULL default 'above';
  ALTER TABLE `{$this->getTable('optionextended/template_option')}` CHANGE `layout` `layout` varchar(64) NOT NULL default 'above';
");

$installer->endSetup(); 

