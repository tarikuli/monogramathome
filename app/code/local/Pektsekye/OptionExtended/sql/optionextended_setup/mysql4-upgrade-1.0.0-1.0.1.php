<?php

$installer = $this;

$installer->startSetup();

$result = $this->_conn->fetchAll("
  SELECT product_id, ox_option_id 
  FROM  `{$this->getTable('optionextended/option')}`      
  WHERE row_id IS NULL  
");

$id1 = $this->_conn->fetchPairs("SELECT product_id, MAX(row_id) FROM `{$this->getTable('optionextended/option')}` GROUP BY `product_id`");  
$id2 = $this->_conn->fetchPairs("SELECT product_id, MAX(row_id) FROM `{$this->getTable('optionextended/value')}` GROUP BY `product_id`"); 
 
foreach ($result as $r){
  if (!isset($id2[$r['product_id']]))
    $id2[$r['product_id']] = 0;
  if (isset($id1[$r['product_id']]) && $id1[$r['product_id']] > $id2[$r['product_id']])    
    $id2[$r['product_id']] = $id1[$r['product_id']];
    
  $id2[$r['product_id']]++;      
  $this->updateTableRow($this->getTable('optionextended/option'), 'ox_option_id', $r['ox_option_id'], 'row_id', $id2[$r['product_id']]);          
}


$result = $this->_conn->fetchAll("
  SELECT oxo.product_id, ox_value_id 
  FROM `{$this->getTable('optionextended/option')}` oxo 
  JOIN `{$this->getTable('catalog/product_option_type_value')}` potv 
    ON potv.option_id = oxo.option_id
  JOIN `{$this->getTable('optionextended/value')}` oxv
    ON oxv.option_type_id = potv.option_type_id             
  WHERE oxv.row_id IS NULL
");      


foreach ($result as $r){
  if (!isset($id2[$r['product_id']]))
    $id2[$r['product_id']] = 0;
  if (isset($id1[$r['product_id']]) && $id1[$r['product_id']] > $id2[$r['product_id']])    
    $id2[$r['product_id']] = $id1[$r['product_id']];
    
  $id2[$r['product_id']]++;    
  $this->updateTableRow($this->getTable('optionextended/value'), 'ox_value_id', $r['ox_value_id'], 'row_id', $id2[$r['product_id']]);        
}


$installer->run("
  ALTER TABLE `{$this->getTable('optionextended/value')}` 
  CHANGE `children` `children` text NOT NULL default '',
  CHANGE `row_id` `row_id` smallint unsigned NOT NULL;
");
  
$this->_conn->addColumn($this->getTable('optionextended/option'), "code", "VARCHAR( 64 ) NOT NULL AFTER `product_id`"); 
  
$installer->run("

  UPDATE `{$this->getTable('optionextended/option')}` SET `code` = concat('opt-',`product_id`,'-',`option_id`) WHERE `code` = '';

  ALTER TABLE `{$this->getTable('optionextended/option')}` ADD UNIQUE (`code`);

  DROP TABLE IF EXISTS `{$this->getTable('optionextended/import')}`;

");

$installer->endSetup(); 

