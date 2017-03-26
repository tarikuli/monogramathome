<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
	$installer->getTable('imagegallery/image'),
    'image_row',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true,
        'comment' => 'Image Row'
    )
);

$installer->getConnection()->addColumn(
	$installer->getTable('imagegallery/image'),
    'image_order',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true,
        'comment' => 'Image Order'
    )
);

$installer->endSetup();

?>
