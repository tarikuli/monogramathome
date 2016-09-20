<?php

class Egs_Firstdata_Model_Source_Type
{
    
    public function toOptionArray()
    {
        return
        array(
             array("value"=>'01',"label"=> 'Authorize Only'),
             array("value"=>'00',"label"=> 'Authorize and Capture'),
        );
    }
    

}