<?php

$installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
$installer->startSetup();

$installer->addAttribute('catalog_category', 'include_menu',  array(
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Include Menu',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'opc/attribute_source_menu_type',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible'           => true,
    'required'          => true,
    'user_defined'      => false,
    'default'           => 0,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'group'             => 'Display Settings'
));
$installer->endSetup();