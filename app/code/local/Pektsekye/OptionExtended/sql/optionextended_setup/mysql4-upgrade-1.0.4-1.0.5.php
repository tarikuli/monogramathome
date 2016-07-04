<?php

$installer = $this;

$installer->startSetup();

$lastTemplateOptionId = (int) $this->_conn->fetchOne("SELECT `option_id` FROM `{$this->getTable('optionextended/template_option')}` ORDER BY `option_id` DESC LIMIT 1 ");

if ($lastTemplateOptionId < 1000000){

  $lastOptionId = (int) $this->_conn->fetchOne("SELECT `option_id` FROM `{$this->getTable('catalog/product_option')}` ORDER BY `option_id` DESC LIMIT 1 ");
  $lastOptionTypeId = (int) $this->_conn->fetchOne("SELECT `option_type_id` FROM `{$this->getTable('catalog/product_option_type_value')}` ORDER BY `option_type_id` DESC LIMIT 1 ");

  $lastOptionId += 1000000;
  $lastOptionTypeId += 1000000;

  $installer->run("
    ALTER TABLE `{$this->getTable('optionextended/template_option')}` AUTO_INCREMENT = {$lastOptionId};
    ALTER TABLE `{$this->getTable('optionextended/template_value')}` AUTO_INCREMENT = {$lastOptionTypeId};
  ");
}

$installer->endSetup(); 

