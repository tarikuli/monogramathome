<?php
class Egs_Firstdata_Model_Source_Cardtype
{
    
    public function toOptionArray()
    {
        return
        array(
             array("value"=>'Visa',"label"=> 'Visa'),
             array("value"=>'Mastercard',"label"=> 'Mastercard'),
             array("value"=>'Amex',"label"=> 'Amex'),
             array("value"=>'Americanexpress ',"label"=> 'American Express')
              
        );
    }
    

}