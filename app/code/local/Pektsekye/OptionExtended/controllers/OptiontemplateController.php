<?php

class Pektsekye_OptionExtended_OptiontemplateController extends Mage_Adminhtml_Controller_Action
{

  protected function _initTemplate()
  {
      $templateId = (int) $this->getRequest()->getParam('template_id');    
      $template = Mage::getModel('optionextended/template');
      
      if ($templateId){
        $template->load($templateId);
        $productIds = $template->getResource()->getProductIds($templateId);
        $template->setProductIds($productIds);        
        $template->setProductIdsString(implode(',', $productIds));
      } else {
        $template->setIsActive(1);
      }
      
      Mage::register('current_template', $template);
      return $this;
  }
  
  public function indexAction()
  {   
      if (version_compare(Mage::getVersion(), '1.4.0.0') >= 0)
        $this->_title($this->__('Option Templates'));

      $this->loadLayout();

      $this->_setActiveMenu('catalog/optiontemplate');

      $this->_addContent(
          $this->getLayout()->createBlock('optionextended/optiontemplate', 'optiontemplate')
      );


      $this->_addBreadcrumb($this->__('Option Templates'), $this->__('Option Templates'));

      $this->renderLayout();
  }


  public function editAction()
  {
      $this->_initTemplate();
      $template = Mage::registry('current_template');
          
      $this->loadLayout();
      
      if (version_compare(Mage::getVersion(), '1.4.0.0') >= 0)
        $this->_title($template->getId() ? $template->getTitle() : Mage::helper('adminhtml')->__('New Template'));

      $this->_setActiveMenu('catalog/optiontemplate');
      
      $this->renderLayout();
  }
 
 
	public function newAction() {
		$this->_forward('edit');
	}
	
	
	
	
  
	public function saveAction() {
	
		if ($post = $this->getRequest()->getPost()) {

      $this->_initTemplate();
      $template = Mage::registry('current_template');
			$template->setTitle($post['template_title']);
			$template->setCode($post['template_code']);		
		  $template->setIsActive($post['is_active']);
		  $template->setProductIds(explode(',', $post['product_ids_string']));
		  
			try {

        $template->save();


				
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Template was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('template_id' => $template->getId(), 'tab' => $this->getRequest()->getParam('tab')));
					return;
				}
				$this->_redirect('*/*/');
				return;
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        $this->_redirect('*/*/edit', array('_current'=>true));
        return;
      }
    } 
    Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find template to save'));
    $this->_redirect('*/*/');
	}




	public function duplicateAction() {
	  $templateId = $this->getRequest()->getParam('template_id');
		if (!empty($templateId)) {
			try {
				$newTemplateId = Mage::getResourceModel('optionextended/template')->duplicate((int) $templateId);					 
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Template was successfully duplicated'));
			} catch (Exception $e) {
			  $newTemplateId = $templateId;
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		
    $this->_redirect('*/*/edit', array('template_id' => $newTemplateId));     
	}


   
	public function deleteAction() {
		if( $this->getRequest()->getParam('template_id') > 0 ) {
			try {
				$model = Mage::getModel('optionextended/template');
				 
				$model->setId($this->getRequest()->getParam('template_id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Template was successfully deleted'));
				$this->_redirect('*/*/');
				return;				
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('_current'=>true));
				return;
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $ids = $this->getRequest()->getParam('ids');
        if(!is_array($ids)) {
		    	Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
				          $model = Mage::getModel('optionextended/template');				 
				          $model->setId($id)->delete();                    
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
	
    public function massStatusAction() {
        $ids = $this->getRequest()->getParam('ids');
        if (!is_array($ids)) {
			    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
				          $model = Mage::getModel('optionextended/template')
				                  ->load($id)
                          ->setIsActive($this->getRequest()->getParam('status'));
                  $model->save();                    
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('optionextended')->__('Total of %d record(s) have been updated.', count($ids))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    } 

    public function productsGridAction()
    {
      $this->_initTemplate();
      $this->getResponse()->setBody($this->getLayout()->createBlock('optionextended/optiontemplate_edit_tab_products_grid')->toHtml());
    }

    public function gridAction()
    {
      $this->loadLayout();       
      $this->getResponse()->setBody($this->getLayout()->createBlock('optionextended/optiontemplate_grid')->toHtml());
    }          
  	
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/optionextended');
    }    	
  	
}
