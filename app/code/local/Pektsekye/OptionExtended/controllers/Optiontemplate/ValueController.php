<?php

class Pektsekye_OptionExtended_Optiontemplate_ValueController extends Mage_Adminhtml_Controller_Action
{

  protected function _initValue()
  {
      $templateId = (int) $this->getRequest()->getParam('template_id');  
      $optionId   = (int) $this->getRequest()->getParam('option_id');
      $valueId    = (int) $this->getRequest()->getParam('value_id');
      $storeId    = (int) $this->getRequest()->getParam('store');                  
      $value      = Mage::getModel('optionextended/template_value');
      
      if ($valueId){
        $value->load($valueId);
        $value->loadStoreFields($storeId);             
      } else {
        $value->setOptionId($optionId);        
      }
      
      $value->setTemplateId($templateId);      
      $value->setStoreId($storeId);
            
      Mage::register('current_value', $value);
      return $this;
  }
  
  public function indexAction()
  {

      if (version_compare(Mage::getVersion(), '1.4.0.0') >= 0)  
        $this->_title($this->__('Option Templates'));

      $this->loadLayout();

      $this->_setActiveMenu('catalog/optiontemplate');

      Mage::register('current_template_id', (int) $this->getRequest()->getParam('template_id'));
      Mage::register('current_option_id', (int) $this->getRequest()->getParam('option_id'));     
       
      $this->_addContent(
          $this->getLayout()->createBlock('optionextended/optiontemplate_value', 'optiontemplate_value')
      );


      $this->_addBreadcrumb($this->__('Option Templates'), $this->__('Values'));

      $this->renderLayout();
  }


  public function editAction()
  {
      $this->_initValue();
      $value = Mage::registry('current_value');
          
      $this->loadLayout();
      
      if (version_compare(Mage::getVersion(), '1.4.0.0') >= 0)
        $this->_title($value->getId() ? $value->getTitle() : $this->__('New Value'));

      $this->_setActiveMenu('catalog/optiontemplate');
      
      $this->renderLayout();
  }
 
 
	public function newAction() {
		$this->_forward('edit');
	}
	
	
	
	
  
	public function saveAction() {
	
		if ($post = $this->getRequest()->getPost()) {
		

      $this->_initValue();
      $value = Mage::registry('current_value');      

	    if ($post['row_id'] != '')
	      $rowId = $post['row_id'];
	    else 
	      $rowId = (int) Mage::getResourceModel('optionextended/template_option')->getLastRowId($value->getTemplateId()) + 1;		    
		    
		  $value->setRowId($rowId);   
			if (isset($post['title']))   		            
			  $value->setTitle($post['title']);	
			if (isset($post['price']))			  		
        $value->setPrice($post['price']);
      $value->setPriceType($post['price_type']);
      $value->setSku($post['sku']); 
			$value->setSortOrder($post['sort_order']);
			$value->setChildren($post['children']);

			$image = '';
			$imageInfo = array();
			if ($post['image'] != '') {			
				$imageInfo = Zend_Json::decode($post['image']);
				if (isset($imageInfo['file'])){		
					$image = $this->_moveImageFromTmp($imageInfo['file']);
				}	
			}				
			if (isset($imageInfo['file']) || !isset($imageInfo['url']))	
			  $value->setImage($image);
			  
			if (isset($post['description']))   			  			
			  $value->setDescription($post['description']);
																						

      if ($value->getStoreId() != 0){			
			  if (isset($post['title_use_default']))           
			    $value->setTitleUseDefault(1);
			  if (isset($post['description_use_default']))   			    
			    $value->setDescriptionUseDefault(1);
			  if (isset($post['price_use_default'])) 
			  	$value->setPriceUseDefault(1);
			}
		  
			try {

        $value->save();

	
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Value was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('template_id' => $this->getRequest()->getParam('template_id'), 'option_id' => $this->getRequest()->getParam('option_id'), 'value_id' => $value->getId(), 'store'=> (int)$this->getRequest()->getParam('store')));
					return;
				}
				$this->_redirect('*/*/', array('template_id' => $this->getRequest()->getParam('template_id'), 'option_id' => $this->getRequest()->getParam('option_id')));
				return;
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        $this->_redirect('*/*/edit', array('template_id' => $this->getRequest()->getParam('template_id'), 'option_id' => $this->getRequest()->getParam('option_id'), 'value_id' => $value->getId(), 'store'=> (int)$this->getRequest()->getParam('store')));
        return;
      }
    }
    
    Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find value to save'));
    $this->_redirect('*/*/', array('template_id' => $this->getRequest()->getParam('template_id'), 'option_id' => $this->getRequest()->getParam('option_id')));
	}



	public function deleteAction() {
		if( $this->getRequest()->getParam('value_id') > 0 ) {
			try {

        Mage::getResourceModel('optionextended/template_value')->deleteValuesWithChidrenUpdate((int) $this->getRequest()->getParam('template_id'), (int) $this->getRequest()->getParam('value_id'));

				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Value was successfully deleted'));
				$this->_redirect('*/*/', array('template_id' => $this->getRequest()->getParam('template_id'), 'option_id' => $this->getRequest()->getParam('option_id')));
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('_current'=>true));
				return;
			}
		}
		$this->_redirect('*/*/', array('template_id' => $this->getRequest()->getParam('template_id'), 'option_id' => $this->getRequest()->getParam('option_id')));
	}

    public function massDeleteAction() {
        $ids = $this->getRequest()->getParam('ids');
        if(!is_array($ids)) {
		    	Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                Mage::getResourceModel('optionextended/template_value')->deleteValuesWithChidrenUpdate((int) $this->getRequest()->getParam('template_id'), $ids);

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/', array('template_id' => $this->getRequest()->getParam('template_id'), 'option_id' => $this->getRequest()->getParam('option_id')));
    }  


    /**
     * Move image from temporary directory to normal
     *
     * @param string $file
     * @return string
     */
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
	
    /**
     * Retrive media config
     *
     * @return Mage_Catalog_Model_Product_Media_Config
     */
    protected function _getMadiaConfig()
    {
        return Mage::getSingleton('catalog/product_media_config');
    }	 
    
    public function gridAction()
    {
      $this->loadLayout();       
      $this->getResponse()->setBody($this->getLayout()->createBlock('optionextended/optiontemplate_value_grid')->toHtml());
    }    
    
    protected function _isAllowed()
    {
        return true;
    }    
         	
}
