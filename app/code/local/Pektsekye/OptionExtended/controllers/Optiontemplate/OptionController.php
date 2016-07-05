<?php

class Pektsekye_OptionExtended_Optiontemplate_OptionController extends Mage_Adminhtml_Controller_Action
{

  protected function _initOption()
  {
      $templateId = (int) $this->getRequest()->getParam('template_id');
      $optionId   = (int) $this->getRequest()->getParam('option_id');
      $storeId    = (int) $this->getRequest()->getParam('store');                  
      $option     = Mage::getModel('optionextended/template_option');
      
      if ($optionId){
        $option->load($optionId);
        $option->loadStoreFields($storeId);
        $sd = explode(',', $option->getSelectedByDefault());
        if ($option->getType() == 'radio' || $option->getType() == 'drop_down')
          $option->setSd($sd);
        elseif ($option->getType() == 'checkbox' || $option->getType() == 'multiple')
          $option->setSdMultiple($sd);                       
      } else {
        $option->setTemplateId($templateId);
      }
      
      $option->setStoreId($storeId);
            
      Mage::register('current_option', $option);
      return $this;
  }
  
  public function indexAction()
  {     
      if (version_compare(Mage::getVersion(), '1.4.0.0') >= 0)
        $this->_title($this->__('Option Templates'));

      $this->loadLayout();

      $this->_setActiveMenu('catalog/optiontemplate');
      
      $this->_addContent(
          $this->getLayout()->createBlock('optionextended/optiontemplate_option', 'optiontemplate_option')
      );


      $this->_addBreadcrumb($this->__('Option Templates'), Mage::helper('adminhtml')->__('Options'));

      $this->renderLayout();
  }


  public function editAction()
  {
      $this->_initOption();
      $option = Mage::registry('current_option');
          
      $this->loadLayout();
      if (version_compare(Mage::getVersion(), '1.4.0.0') >= 0)
        $this->_title($option->getId() ? $option->getTitle() : $this->__('New Option'));

      $this->_setActiveMenu('catalog/optiontemplate');
      
      $this->renderLayout();
  }
 
 
	public function newAction() {
		$this->_forward('edit');
	}
	

	public function saveAction() {
	
		if ($post = $this->getRequest()->getPost()) {

      $this->_initOption();
      $option = Mage::registry('current_option');
      $coreOptionModel = Mage::getModel('catalog/product_option');      
      $group  = $coreOptionModel->getGroupByType($post['type']);
   
      if (!is_null($option->getId()) && $group != $coreOptionModel->getGroupByType($option->getType())){
        if ($group != 'select')
			    $option->deleteValues();
			  else
          $option->deletePrice();			              
      }
      
      $rowId = null;
      if ($group != 'select'){
        if ($post['row_id'] != '')
          $rowId = $post['row_id'];
        else 
          $rowId = (int) $option->getLastRowId() + 1;
      }		    
	    
	    $option->setRowId($rowId);
			if (isset($post['title']))      
			  $option->setTitle($post['title']);
			$option->setType($post['type']);
			$option->setIsRequire($post['is_require']);
			$option->setSortOrder($post['sort_order']);
			$code = $post['code'] != '' ? $post['code'] : (is_null($option->getId()) ? 'opt-'. $option->getTemplateId() .'-'. $option->getNextId() : $option->getCode($code));			
			$option->setCode($code);
			if (isset($post['note']))
			  $option->setNote($post['note']);			
		  $option->setLayout($post['layout']);
		  $option->setPopup(isset($post['popup']) ? 1 : 0);
		        
      if ($post['type'] == 'radio' || $post['type'] == 'drop_down'){
        $sd = $post['sd']; 
      } else { 
        $sd = '';                   
        if (isset($post['sd_multiple'])){
          if ($post['sd_multiple'][0] == '-1')
            unset($post['sd_multiple'][0]);
          $sd = implode(',', $post['sd_multiple']);
        }
      }
      $option->setSelectedByDefault($sd);
    	        			  			  
			if (isset($post['price']))
        $option->setPrice($post['price']);
      $option->setPriceType($post['price_type']);
      $option->setSku($post['sku']);        
      $option->setMaxCharacters($post['max_characters']);
      $option->setFileExtension($post['file_extension']);
      $option->setImageSizeX($post['image_size_x']);
      $option->setImageSizeY($post['image_size_y']);	      																											

      if ($option->getStoreId() != 0){
			  if (isset($post['title_use_default']))      			
			    $option->setTitleUseDefault(1);
			  if (isset($post['note_use_default']))  			    
			    $option->setNoteUseDefault(1);
			  if (isset($post['price_use_default'])) 
			  	$option->setPriceUseDefault(1);
			}
		  
			try {

        $option->save();

	
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Option was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('template_id' => $this->getRequest()->getParam('template_id'), 'option_id' => $option->getId(), 'store'=> (int)$this->getRequest()->getParam('store')));
					return;
				}
				$this->_redirect('*/*/', array('template_id' => $this->getRequest()->getParam('template_id')));
				return;
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        $this->_redirect('*/*/edit', array('template_id' => $this->getRequest()->getParam('template_id'), 'option_id' => $option->getId(), 'store'=> (int)$this->getRequest()->getParam('store')));
        return;
      }
    }
    
    Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find option to save'));
    $this->_redirect('*/*/', array('template_id' => $this->getRequest()->getParam('template_id')));
	}



	public function importAction() {
      $this->loadLayout();
      
      if (version_compare(Mage::getVersion(), '1.4.0.0') >= 0)
        $this->_title($this->__('Import Options From Product'));

      $this->_setActiveMenu('catalog/optiontemplate');      
      
      $this->renderLayout();
	}

	public function doimportAction() {
	  $productId = $this->getRequest()->getPost('product_id');
		if (!empty($productId)) {
			try {
				Mage::getResourceModel('optionextended/template_option')->importOptionsFromProduct((int) $this->getRequest()->getParam('template_id'), (int) $productId);
					 
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Options were successfully imported'));
				$this->_redirect('*/*/', array('template_id' => $this->getRequest()->getParam('template_id')));
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/import', array('template_id' => $this->getRequest()->getParam('template_id')));
				return;				
			}
		}
    $this->_redirect('*/*/import', array('template_id' => $this->getRequest()->getParam('template_id'))); 
	}


	public function duplicateAction() {
	  $optionId = $this->getRequest()->getParam('option_id');
		if (!empty($optionId)) {
			try {
				$newOptionId = Mage::getResourceModel('optionextended/template_option')->duplicate((int) $optionId);					 
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Option was successfully duplicated'));
			} catch (Exception $e) {
				$newOptionId = $optionId;
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		
    $this->_redirect('*/*/edit', array('template_id' => $this->getRequest()->getParam('template_id'), 'option_id' => $newOptionId, 'store'=> (int)$this->getRequest()->getParam('store')));     
	}
   
	public function deleteAction() {
	  $optionId = $this->getRequest()->getParam('option_id');
		if (!empty($optionId)) {
			try {
				Mage::getResourceModel('optionextended/template_option')->deleteOptionsWithChidrenUpdate((int) $this->getRequest()->getParam('template_id'), (int) $optionId);
					 
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Option was successfully deleted'));
				$this->_redirect('*/*/', array('template_id' => $this->getRequest()->getParam('template_id')));
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('_current'=>true));
				return;
			}
		}
		$this->_redirect('*/*/', array('template_id' => $this->getRequest()->getParam('template_id')));

	}

  public function massDeleteAction() {
    $ids = $this->getRequest()->getParam('ids');
    if(!is_array($ids)) {
      Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
    } else {
      try {
          Mage::getResourceModel('optionextended/template_option')->deleteOptionsWithChidrenUpdate((int) $this->getRequest()->getParam('template_id'), $ids);

          Mage::getSingleton('adminhtml/session')->addSuccess(
              Mage::helper('adminhtml')->__(
                  'Total of %d record(s) were successfully deleted', count($ids)
              )
          );
      } catch (Exception $e) {
          Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
      }
    }      
    $this->_redirect('*/*/', array('template_id' => $this->getRequest()->getParam('template_id')));      
  }  
  
  public function gridAction()
  {
    $this->loadLayout();   
    $this->getResponse()->setBody($this->getLayout()->createBlock('optionextended/optiontemplate_option_grid')->toHtml());
  }       
        
  protected function _isAllowed()
  {
      return true;
  }     	
     	
}
