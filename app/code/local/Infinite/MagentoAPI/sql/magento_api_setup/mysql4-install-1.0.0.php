<?php

$installer = $this;

$installer->startSetup();

$installer->addAttribute('customer', 'username', array(
	'label'                     => 'Username',
    'type'                      => 'varchar',
	'input'                     => 'text',
	'frontend_class'            => '',
    'frontend'                  => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'required'                  => false,
    'visible_on_front'          => false,
    'used_in_product_listing'   => false,
    'sort_order'                => 200,
));

$installer->endSetup();

?>