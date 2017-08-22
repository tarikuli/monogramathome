<?php 
/* We will redirect to the checkout/cart for exemple */
class Eps_Customizationurl_Model_Observer extends Varien_Event_Observer {
    public function epsUrlCreator($observer) {
        Mage::log('Starting EPS URL Creator Observer!', null, 'system.log', true);

        // Get the quote item and the product associated to it
        $quoteItem = $observer->getItem();
        $product = $quoteItem->getProduct();

        // Read the eps_url field from the product that comes from the frontend
        $options = $product->getTypeInstance(true)->getOrderOptions($product);
        $epsUrl = '';
        if(isset($options['info_buyRequest'])) {
            $epsUrl = $options['info_buyRequest']['eps_url'];
        }
        Mage::log($epsUrl, null, 'system.log', true);

        // If we have a valid EPS url add it to the order item
        if ($epsUrl != '' && $epsUrl != null) {
            Mage::log('Will add EPS url', null, 'system.log', true);
            $orderItem = $observer->getOrderItem();
            $options = $orderItem->getProductOptions();
            
            //Create custom option and add it to the order item
            $additionalOptions = array(
                array(
                    'code' => 'eps_url',
                    'label' => 'Custom EPS download link',
                    'value' =>  $epsUrl
                )
            );
            $options['additional_options'] = $additionalOptions;
            $orderItem->setProductOptions($options);
        }
    }
}
?>