<?php
/**
 * Created by PhpStorm.
 * User: julfiker
 * Date: 4/24/16
 * Time: 1:16 AM
 */

// Report all PHP errors (see changelog)
error_reporting(E_ALL);
ini_set("display_errors", 1);
// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

//require 'app/bootstrap.php';
require 'app/Mage.php';

Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

$importDir = Mage::getBaseDir('media') . DS . 'catalog/product/';
$data = $_REQUEST['data'];

foreach($data as $dt) {
    foreach($dt  as $key => $val) {
        //$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $key);
        $obj = Mage::getModel('catalog/product');

        $product_id = $obj->getIdBySku($key);

        $product = $obj->load($product_id);

        if($product->getOptions()){ foreach ($product->getOptions() as $opt) { $opt->delete(); } $product->setHasOptions(0)->save(); }

        Mage::getSingleton('catalog/product_option')->unsetOptions(); // forget me and Magento will RAPE YOU
        $new_options = array();
        foreach($val as $v) {
            if ($v['type'] == "select") {
                $options = array();
                $h = 0;
                foreach ($v['options'] as $k => $op) {
                    if ($h > 0) {
                        $options[] = array(
                            'option_type_id' => -1,
                            'is_delete' => null,
                            'title' => $op['text'],
                            'price' => $op['price'],
                            'price_type' => 'fixed',
                            'sku' => "",
                            'sort_order' => $k
                        );
                    }
                    $h++;
                }
                $new_options[]  = $opt = array(
                    'title' => $v['label'],
                    'type' => 'drop_down',
                    'previous_type' => null,
                    'previous_group' => 'select',
                    'is_require' => 1,
                    'is_delete' => null,
                    'sort_order' => $v['short'],
                    'values' => $options
                );
            }
            else if ($v['type'] == "text") {
                $new_options[] = $opt = array(
                    'title' => $v['label'],
                    'type' => 'field',
                    'previous_type' => null,
                    'previous_group' => 'text',
                    'is_require' => 1,
                    'is_delete' => null,
                    'max_characters' => $v['max'],
                    'sort_order' => $v['short']
                );
            }
        }
        try {
            Mage::getSingleton('catalog/product_option')->unsetOptions();
            $product->setProductOptions($new_options);
            $product->setCanSaveCustomOptions(true);
            $product->save();
            echo "success for productId = ".$product->getId();
        }
        catch (Exception $e) {
            echo "faild for product = ".$product->getId();
        }
        //DELETE FROM `core_resource` WHERE `code` = 'optionextended_setup'
    }
}