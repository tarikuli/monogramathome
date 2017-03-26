<?php

$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$setup->addAttribute("customer", "devote_building_business",  array(
    "type"       => "varchar",
    "backend"    => "",
    "label"      => "How much time do you have to devote to building your business?",
    "input"      => "select",
    "source"     => "opc/attribute_option_devoteBuildingBusiness",
    "visible"    => true,
    "required"   => false,
    "default"    => "",
    "frontend"   => "",
    "unique"     => false,
));

$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "devote_building_business");

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'devote_building_business',
    '270'
);

$attribute->setData("used_in_forms", array("adminhtml_customer"))
    ->setData("is_used_for_customer_segment", true)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 1)
    ->setData("is_visible", 1)
    ->setData("sort_order", 100);
$attribute->save();

$installer->endSetup();