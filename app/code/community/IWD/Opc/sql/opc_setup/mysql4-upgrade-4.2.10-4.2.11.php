<?php
//die("DIED");

$installer = $this;
$installer->startSetup();


$table = $installer->getConnection()
		->newTable($installer->getTable('opc/address'))
		->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'identity'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Entity ID')
		->addColumn('customer_id',Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'nullable'  => false,
        ), 'Customer ID')
        ->addColumn('address', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => true,
        ), 'Address')
        ->addColumn('state', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ), 'State')
        ->addColumn('city', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ), 'City')
        ->addColumn('country', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ), 'Country')
        ->addColumn('zipcode', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ), 'ZipCode')
        ->addColumn('telephone', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ), 'Telephone')
        ->addColumn('fax', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ), 'Fax')
        ->setComment('Address Table');

$installer->getConnection()->createTable($table);
$installer->endSetup();



?>