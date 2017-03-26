<?php

$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$setup->addAttribute("customer", "how_familiar_social_media",  array(
    "type"       => "varchar",
    "backend"    => "",
    "label"      => "How familiar are you with using social media to grow your business?",
    "input"      => "select",
    "source"     => "opc/attribute_option_howFamiliarSocialMedia",
    "visible"    => true,
    "required"   => false,
    "default"    => "",
    "frontend"   => "",
    "unique"     => false,
));

$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "how_familiar_social_media");

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'how_familiar_social_media',
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