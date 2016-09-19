<?php
/**
 * Created by PhpStorm.
 * User: julfiker
 * Date: 5/26/16
 * Time: 12:51 AM
 */

class Julfiker_ProductExport_Adminhtml_ExportController extends Mage_Adminhtml_Controller_Action
{
    public function jewelryAction() {
        $file_name = "jewelry_products_".time().".csv";
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=$file_name");

        try  {
        Mage::app();
        $products = Mage::getModel("catalog/product")->getCollection();
        $products->addAttributeToFilter('attribute_set_id','9')
            ->addAttributeToSelect('*');

        $fopen = fopen('php://output', 'w');

        $csvHeader = array("sku", "name", "price", "special_price" ,"short_description",  "description", "specifications", "status", "weight", "tax_class_id", "qty");
        fputcsv( $fopen , $csvHeader,",");
        foreach ($products as $product){
//            var_dump($product);
//            die();
            $sku = $product->getSku();
            $name = $product->getName();
            $price = $product->getPrice();
            $special_price = $product->getSpecialPrice();
            $shortDescription = $product->getShortDescription();
            $description = $product->getDescription();
            $specifications = ($product->getSpecifications())?$product->getSpecifications():"";
            $status = $product->getStatus();
            $weight = $product->getWeight();
            $tax_class_id = $product->getTaxClassId();
            $qty = (int)Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($product)->getQty();

            fputcsv($fopen, array($sku,$name, $price, $special_price, $shortDescription,$description,$specifications,$status,$weight, $tax_class_id, $qty), ",");
        }
        fclose($fopen );
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function generalAction() {
        $file_name = "general_products_".time().".csv";
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=$file_name");

        try  {
            Mage::app();
            $products = Mage::getModel("catalog/product")->getCollection();
            $products->addAttributeToFilter('attribute_set_id','4')
                ->addAttributeToSelect('*');

            $fopen = fopen('php://output', 'w');

            $csvHeader = array("sku", "name", "price", "special_price" ,"short_description",  "description", "specifications", "status", "weight", "tax_class_id", "qty");
            fputcsv( $fopen , $csvHeader,",");
            foreach ($products as $product){
//            var_dump($product);
//            die();
                $sku = $product->getSku();
                $name = $product->getName();
                $price = $product->getPrice();
                $special_price = $product->getSpecialPrice();
                $shortDescription = $product->getShortDescription();
                $description = $product->getDescription();
                $specifications = ($product->getSpecifications())?$product->getSpecifications():"";
                $status = $product->getStatus();
                $weight = $product->getWeight();
                $tax_class_id = $product->getTaxClassId();
                $qty = (int)Mage::getModel('cataloginventory/stock_item')
                    ->loadByProduct($product)->getQty();

                fputcsv($fopen, array($sku,$name, $price, $special_price, $shortDescription,$description,$specifications,$status,$weight, $tax_class_id, $qty), ",");
            }
            fclose($fopen );
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}