<?php

class Pektsekye_OptionExtended_Model_Mysql4_Pickerimage extends Mage_Core_Model_Resource_Db_Abstract
{

    public function _construct()
    {
        $this->_init('optionextended/pickerimage', 'ox_image_id');
    }

  
    public function getImageData()
    {        
      $select = $this->_getReadAdapter()->select()->from($this->getMainTable());  
               
      return $this->_getReadAdapter()->fetchAll($select);                                
    }     
    
    
    public function saveImages($images)
    {         
       
      if (count($images) == 0)
        return $this;
                
      $data = array();      
      foreach ($images as $imageId => $value){
      
        $image = '';
        if (preg_match("/.tmp$/i", $value['image'])) {
          $image = $this->_moveImageFromTmp($value['image']);
        }	elseif (isset($value['image_saved_as']) && $value['image_saved_as'] != '' && (!isset($value['delete_image']) || $value['delete_image'] == '')){
          $image = $value['image_saved_as'];          
        }	 
               
        if ($image == ''){
          $this->_getWriteAdapter()->delete($this->getTable('optionextended/pickerimage'), $this->_getWriteAdapter()->quoteInto('ox_image_id = ?', $imageId));
          continue; 
        }
        
        $data = array(
          'title'       => $value['title'],  
          'image'       => $image                   
        ); 
        
        $statement = $this->_getReadAdapter()->select()
          ->from($this->getMainTable())
          ->where("ox_image_id=?", $imageId);

        if ($this->_getReadAdapter()->fetchRow($statement)) {
            $this->_getWriteAdapter()->update(
              $this->getMainTable(),
              $data,
              "ox_image_id={$imageId}"
            );
        } else {
          $this->_getWriteAdapter()->insert($this->getMainTable(), $data);
        }        
             
      }      


                               
    }   




    protected function _moveImageFromTmp($file)
    {

        $ioObject = new Varien_Io_File();
        $destDirectory = dirname($this->_getMadiaConfig()->getMediaPath($file));

        try {
            $ioObject->open(array('path'=>$destDirectory));
        } catch (Exception $e) {
            $ioObject->mkdir($destDirectory, 0777, true);
            $ioObject->open(array('path'=>$destDirectory));
        }

        if (strrpos($file, '.tmp') == strlen($file)-4) {
            $file = substr($file, 0, strlen($file)-4);
        }

        $destFile = dirname($file) . $ioObject->dirsep()
                  . Varien_File_Uploader::getNewFileName($this->_getMadiaConfig()->getMediaPath($file));
                
        $ioObject->mv(
          $this->_getMadiaConfig()->getTmpMediaPath($file),
          $this->_getMadiaConfig()->getMediaPath($destFile)
        );	

        return str_replace($ioObject->dirsep(), '/', $destFile);
    }


    protected function _getMadiaConfig()
    {
        return Mage::getSingleton('catalog/product_media_config');
    }	
  
}
