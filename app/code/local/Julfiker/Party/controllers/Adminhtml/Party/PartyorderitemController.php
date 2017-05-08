<?php
/**
 * Julfiker_Party extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Julfiker
 * @package        Julfiker_Party
 * @copyright      Copyright (c) 2017
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Party order item admin controller
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Adminhtml_Party_PartyorderitemController extends Julfiker_Party_Controller_Adminhtml_Party
{
    /**
     * init the party order item
     *
     * @access protected
     * @return Julfiker_Party_Model_Partyorderitem
     */
    protected function _initPartyorderitem()
    {
        $partyorderitemId  = (int) $this->getRequest()->getParam('id');
        $partyorderitem    = Mage::getModel('julfiker_party/partyorderitem');
        if ($partyorderitemId) {
            $partyorderitem->load($partyorderitemId);
        }
        Mage::register('current_partyorderitem', $partyorderitem);
        return $partyorderitem;
    }

    /**
     * default action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('julfiker_party')->__('Manage event'))
             ->_title(Mage::helper('julfiker_party')->__('Party order items'));
        $this->renderLayout();
    }

    /**
     * grid action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * edit party order item - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function editAction()
    {
        $partyorderitemId    = $this->getRequest()->getParam('id');
        $partyorderitem      = $this->_initPartyorderitem();
        if ($partyorderitemId && !$partyorderitem->getId()) {
            $this->_getSession()->addError(
                Mage::helper('julfiker_party')->__('This party order item no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getPartyorderitemData(true);
        if (!empty($data)) {
            $partyorderitem->setData($data);
        }
        Mage::register('partyorderitem_data', $partyorderitem);
        $this->loadLayout();
        $this->_title(Mage::helper('julfiker_party')->__('Manage event'))
             ->_title(Mage::helper('julfiker_party')->__('Party order items'));
        if ($partyorderitem->getId()) {
            $this->_title($partyorderitem->getQty());
        } else {
            $this->_title(Mage::helper('julfiker_party')->__('Add party order item'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new party order item action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save party order item - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('partyorderitem')) {
            try {
                $partyorderitem = $this->_initPartyorderitem();
                $partyorderitem->addData($data);
                $partyorderitem->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('julfiker_party')->__('Party order item was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $partyorderitem->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPartyorderitemData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was a problem saving the party order item.')
                );
                Mage::getSingleton('adminhtml/session')->setPartyorderitemData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('julfiker_party')->__('Unable to find party order item to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete party order item - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $partyorderitem = Mage::getModel('julfiker_party/partyorderitem');
                $partyorderitem->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('julfiker_party')->__('Party order item was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error deleting party order item.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('julfiker_party')->__('Could not find party order item to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete party order item - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function massDeleteAction()
    {
        $partyorderitemIds = $this->getRequest()->getParam('partyorderitem');
        if (!is_array($partyorderitemIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('julfiker_party')->__('Please select party order items to delete.')
            );
        } else {
            try {
                foreach ($partyorderitemIds as $partyorderitemId) {
                    $partyorderitem = Mage::getModel('julfiker_party/partyorderitem');
                    $partyorderitem->setId($partyorderitemId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('julfiker_party')->__('Total of %d party order items were successfully deleted.', count($partyorderitemIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error deleting party order items.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass status change - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function massStatusAction()
    {
        $partyorderitemIds = $this->getRequest()->getParam('partyorderitem');
        if (!is_array($partyorderitemIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('julfiker_party')->__('Please select party order items.')
            );
        } else {
            try {
                foreach ($partyorderitemIds as $partyorderitemId) {
                $partyorderitem = Mage::getSingleton('julfiker_party/partyorderitem')->load($partyorderitemId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d party order items were successfully updated.', count($partyorderitemIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error updating party order items.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass event change - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function massEventIdAction()
    {
        $partyorderitemIds = $this->getRequest()->getParam('partyorderitem');
        if (!is_array($partyorderitemIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('julfiker_party')->__('Please select party order items.')
            );
        } else {
            try {
                foreach ($partyorderitemIds as $partyorderitemId) {
                $partyorderitem = Mage::getSingleton('julfiker_party/partyorderitem')->load($partyorderitemId)
                    ->setEventId($this->getRequest()->getParam('flag_event_id'))
                    ->setIsMassupdate(true)
                    ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d party order items were successfully updated.', count($partyorderitemIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error updating party order items.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * export as csv - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function exportCsvAction()
    {
        $fileName   = 'partyorderitem.csv';
        $content    = $this->getLayout()->createBlock('julfiker_party/adminhtml_partyorderitem_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as MsExcel - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function exportExcelAction()
    {
        $fileName   = 'partyorderitem.xls';
        $content    = $this->getLayout()->createBlock('julfiker_party/adminhtml_partyorderitem_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as xml - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function exportXmlAction()
    {
        $fileName   = 'partyorderitem.xml';
        $content    = $this->getLayout()->createBlock('julfiker_party/adminhtml_partyorderitem_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @access protected
     * @return boolean
     * @author Julfiker
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('julfiker_party/partyorderitem');
    }
}
