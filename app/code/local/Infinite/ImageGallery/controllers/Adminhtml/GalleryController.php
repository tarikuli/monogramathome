<?php

class Infinite_ImageGallery_Adminhtml_GalleryController extends Mage_Adminhtml_Controller_action 
{
	protected function _initAction() 
	{
		$this->loadLayout()
			->_setActiveMenu('cms/imagegallery/manage_gallery')
			->_addBreadcrumb(Mage::helper('imagegallery')->__('Image Gallery'), Mage::helper('imagegallery')->__('Image Gallery'));	
		return $this;
	}   
 
	public function indexAction() 
    {
		$this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('imagegallery/adminhtml_gallery'));
		$this->renderLayout();
	}

	public function editAction() 
	{
		$this->_title($this->__('CMS'))
             ->_title($this->__('Image Gallery'))
             ->_title($this->__('Manage Gallery'));

        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('imagegallery/gallery');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('slider')->__('This gallery no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Gallery'));

        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (! empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        Mage::register('gallery_data', $model);

        // 5. Build edit form
        $this->_initAction()
            ->_addBreadcrumb(
                $id ? Mage::helper('imagegallery')->__('Edit Gallery')
                    : Mage::helper('imagegallery')->__('New Gallery'),
                $id ? Mage::helper('imagegallery')->__('Edit Gallery')
                    : Mage::helper('imagegallery')->__('New Gallery'));

        $this->_addContent($this->getLayout()->createBlock('imagegallery/adminhtml_gallery_edit'));

        $this->renderLayout();
	}
 
	public function newAction() 
	{
		$this->_forward('edit');
	}
 
	public function saveAction() 
	{
        $model = Mage::getModel('imagegallery/gallery');

		if ($data = $this->getRequest()->getPost()) 
        {
            $model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
            try 
			{
				$model->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('imagegallery')->__('Gallery was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('imagegallery')->__('Unable to find gallery to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() 
	{
		if( $this->getRequest()->getParam('id') > 0 ) 
		{
			try {
				$model = Mage::getModel('imagegallery/gallery');	 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Gallery was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}
}