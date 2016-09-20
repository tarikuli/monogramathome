<?php

/** @var $installer Pektsekye_OptionConfigurable_Model_Resource_Setup */
$installer = $this;

/**
 * Prepare database for tables setup
 */
$installer->startSetup();


$installer->getConnection()->dropTable($installer->getTable('optionextended/pickerimage'));
$table = $installer->getConnection()
    ->newTable($installer->getTable('optionextended/pickerimage'))   
    ->addColumn('ox_image_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,        
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true
        ), 'OptionExtended PickerImages Image ID')           
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Title')  
    ->addColumn('image', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Image')                         
    ->setComment('OptionExtended PickerImages Image Table');
$installer->getConnection()->createTable($table);


$installer->endSetup();
