<?php

class Egs_Firstdata_Model_Source_Mode
{
    
    public function toOptionArray()
    {
        return
        array(
             array("value"=>'test',"label"=> 'Test Mode'),
             array("value"=>'live',"label"=> 'Live Mode'),
        );
    }
    

}