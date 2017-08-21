<?php
class IWD_Opc_Helper_Subscription extends Mage_Checkout_Helper_Url{

    /**
     * Monthly Subscription
     *
     * @return string
     */
    public function submitSubscription($productId, $cusEmail){

    	 Mage::log('submitSubscription 1 = '.print_r($productId, true), null, 'system.log', true);
    	 Mage::log('submitSubscription 2 = '.print_r($cusEmail, true), null, 'system.log', true);
    	 
    	 return "Yahoo";
    }

}
