<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('imagegallery/gallery'),
    'height_of_icon',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true,
        'comment' => 'Height of Icon'
    )
);

$installer->endSetup();

?>
