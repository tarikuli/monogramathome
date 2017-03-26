<?php

$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$setup->addAttribute("customer", "interested_building_business",  array(
    "type"       => "varchar",
    "backend"    => "",
    "label"      => "Are you interested in building a business to make ends meet monthly or are you interested in replacing your salary?",
    "input"      => "select",
    "source"     => "opc/attribute_option_interestedBuildingBusiness",
    "visible"    => true,
    "required"   => false,
    "default"    => "",
    "frontend"   => "",
    "unique"     => false,
));

$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "interested_building_business");

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'interested_building_business',
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