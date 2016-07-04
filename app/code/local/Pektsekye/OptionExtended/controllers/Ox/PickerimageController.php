<?php

class Pektsekye_OptionExtended_Ox_PickerimageController extends Mage_Adminhtml_Controller_Action
{

  
  public function indexAction()
  {   
    $this->_title($this->__('Picker Images'));
    $this->loadLayout();
    $this->_setActiveMenu('catalog/ox_pickerimage');
    $this->_addContent(
        $this->getLayout()->createBlock('optionextended/adminhtml_ox_pickerimage', 'optionextended')
    );
    $this->_addBreadcrumb($this->__('Picker Images'), $this->__('Picker Images'));
    $this->renderLayout();
  }

	
	public function saveAction() {
	
		if ($images = $this->getRequest()->getPost('values')) {

      $pickerimage = Mage::getModel('optionextended/pickerimage');
			  
			try {

        $pickerimage->saveImages($images);
				
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Images were successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				$this->_redirect('*/*/');
				return;
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/');
        return;
      }
    } 
    Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to save images'));
    $this->_redirect('*/*/');
	}


  protected function _isAllowed()
  {
      return Mage::getSingleton('admin/session')->isAllowed('catalog/ox_pickerimage');
  }        
  	
}
