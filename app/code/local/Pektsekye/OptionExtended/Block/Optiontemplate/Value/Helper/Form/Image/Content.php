<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Value_Helper_Form_Image_Content extends Mage_Adminhtml_Block_Widget
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('optionextended/optiontemplate/helper/image.phtml');
    }

    public function getImageJson()
    {
      $js = '';
      $value = $this->getElement()->getValue();    
      if ($value != '') {
        $image['url'] = $this->helper('catalog/image')->init(Mage::getModel('catalog/product'), 'thumbnail', $value)->keepFrame(true)->resize(100,100)->__toString();
        $js = Zend_Json::encode($image);
      }
      return $js;
    }

    
    public function getConfigJson()
    {
        $config = new Varien_Object();
		    $config->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/catalog_product_gallery/upload')); 		
        $config->setParams(array('form_key' => $this->getFormKey()));

		    $config->setFileField('image');
        $config->setFilters(array(
            'images'    => array(
                'label' => Mage::helper('adminhtml')->__('Images (.gif, .jpg, .png)'),
                'files' => array('*.gif','*.jpg','*.jpeg','*.png')
            )
        ));
        $config->setReplaceBrowseWithRemove(true);
        $config->setWidth('32');
        $config->setHideUploadButton(true);
        return Zend_Json::encode($config->getData());
    }

    public function getDataMaxSizeInBytes()
    {
        return min($this->getInBytes(ini_get('post_max_size')), $this->getInBytes(ini_get('upload_max_filesize')));
    }

    public function getDataMaxSize()
    {
        if ($this->getInBytes(ini_get('post_max_size')) < $this->getInBytes(ini_get('upload_max_filesize')))
          return ini_get('post_max_size');
        return ini_get('upload_max_filesize');
    }

    public function getInBytes($iniSize)
    {
        $size = substr($iniSize, 0, strlen($iniSize)-1);
        $parsedSize = 0;
        switch (strtolower(substr($iniSize, strlen($iniSize)-1))) {
            case 't':
                $parsedSize = $size*(1024*1024*1024*1024);
                break;
            case 'g':
                $parsedSize = $size*(1024*1024*1024);
                break;
            case 'm':
                $parsedSize = $size*(1024*1024);
                break;
            case 'k':
                $parsedSize = $size*1024;
                break;
            case 'b':
            default:
                $parsedSize = $size;
                break;
        }
        return $parsedSize;
    }
}
