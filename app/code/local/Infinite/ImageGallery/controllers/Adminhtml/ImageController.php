<?php

class Infinite_ImageGallery_Adminhtml_ImageController extends Mage_Adminhtml_Controller_action 
{
	protected function _initAction() 
	{
		$this->loadLayout()
			->_setActiveMenu('cms/imagegallery/manage_gallery')
			->_addBreadcrumb(Mage::helper('imagegallery')->__('Manage Images'), Mage::helper('imagegallery')->__('Manage Images'));	
		return $this;
	}   
 
	public function indexAction() 
    {
    	Mage::getSingleton('adminhtml/session')->setGalleryId($this->getRequest()->getParam('gallery_id'));

		$this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('imagegallery/adminhtml_image'));
		$this->renderLayout();
	}

	public function editAction() 
	{
		$this->_title($this->__('CMS'))
             ->_title($this->__('Image Gallery'))
             ->_title($this->__('Manage Images'));

        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('imagegallery/image');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('slider')->__('This image no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getTitle() : $this->__('New Image'));

        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (! empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        Mage::register('image_data', $model);

        // 5. Build edit form
        $this->_initAction()
            ->_addBreadcrumb(
                $id ? Mage::helper('imagegallery')->__('Edit Image')
                    : Mage::helper('imagegallery')->__('New Image'),
                $id ? Mage::helper('imagegallery')->__('Edit Image')
                    : Mage::helper('imagegallery')->__('New Image'));

        $this->_addContent($this->getLayout()->createBlock('imagegallery/adminhtml_image_edit'));

        $this->renderLayout();
	}
 
	public function newAction() 
	{
		$this->_forward('edit');
	}
 
	public function saveAction() 
	{
        $model = Mage::getModel('imagegallery/image');

		if ($data = $this->getRequest()->getPost()) 
        {
        	if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') 
            {
          		try 
          		{
            		$path = Mage::helper('imagegallery')->getImageDir();
            		$uploader = new Varien_File_Uploader('image');
            		$uploader->setAllowedExtensions(array('jpg','jpeg','png','gif'));
            		$uploader->setAllowCreateFolders(true);
            		$uploader->setAllowRenameFiles(true);
            		$uploader->setFilesDispersion(true);
            		$uploader->save($path, $_FILES['image']['name']);
                    $data['image'] = Mage::helper('imagegallery')->getFilePath() . $uploader->getUploadedFileName();
          		}
          		catch (Exception $e){
            		echo 'Error Message: '.$e->getMessage();
                    die("DIED");
          		}
			}
            else if(isset($data['image']['delete']))
            {
                $data['image'] = null;
            }
            else if(isset($data['image']['value']))
            {
                $data['image'] = $data['image']['value'];
            }

            $model->setData($data)
            	->setGalleryId(Mage::getSingleton('adminhtml/session')->getGalleryId())
				->setId($this->getRequest()->getParam('id'));
			
            try 
			{
				$model->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('imagegallery')->__('Gallery was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				$this->_redirect('*/*/', array('gallery_id' => Mage::getSingleton('adminhtml/session')->getGalleryId()));
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('imagegallery')->__('Unable to find gallery to save'));
        $this->_redirect('*/*/', array('gallery_id' => Mage::getSingleton('adminhtml/session')->getGalleryId()));
	}
 
	public function deleteAction() 
	{
		if( $this->getRequest()->getParam('id') > 0 ) 
		{
			try {
				$model = Mage::getModel('imagegallery/image');	 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Image was successfully deleted'));
				$this->_redirect('*/*/', array('gallery_id' => Mage::getSingleton('adminhtml/session')->getGalleryId()));
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		$this->_redirect('*/*/');
	}
}