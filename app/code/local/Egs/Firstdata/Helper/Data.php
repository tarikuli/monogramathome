<?php

class Egs_Firstdata_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function typechance($type)
    {
        if($type == '00')
        {
           $type = "Authorize and Capture" ;
        }
        elseif($type == '01')
        {
             $type = "Authorize Only" ;
        }
        else
        {
            $type = "firstdataGGe4" ;
        }
        
       return  $type;
    }
}