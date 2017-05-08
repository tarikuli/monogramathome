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
 * Party_success_promotion admin controller
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Adminhtml_Party_PartysuccesspromotionController extends Julfiker_Party_Controller_Adminhtml_Party
{
    /**
     * init the party_success_promotion
     *
     * @access protected
     * @return Julfiker_Party_Model_Partysuccesspromotion
     */
    protected function _initPartysuccesspromotion()
    {
        $partysuccesspromotionId  = (int) $this->getRequest()->getParam('id');
        $partysuccesspromotion    = Mage::getModel('julfiker_party/partysuccesspromotion');
        if ($partysuccesspromotionId) {
            $partysuccesspromotion->load($partysuccesspromotionId);
        }
        Mage::register('current_partysuccesspromotion', $partysuccesspromotion);
        return $partysuccesspromotion;
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
             ->_title(Mage::helper('julfiker_party')->__('Party_success_promotion'));
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
     * edit party_success_promotion - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function editAction()
    {
        $partysuccesspromotionId    = $this->getRequest()->getParam('id');
        $partysuccesspromotion      = $this->_initPartysuccesspromotion();
        if ($partysuccesspromotionId && !$partysuccesspromotion->getId()) {
            $this->_getSession()->addError(
                Mage::helper('julfiker_party')->__('This party_success_promotion no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getPartysuccesspromotionData(true);
        if (!empty($data)) {
            $partysuccesspromotion->setData($data);
        }
        Mage::register('partysuccesspromotion_data', $partysuccesspromotion);
        $this->loadLayout();
        $this->_title(Mage::helper('julfiker_party')->__('Manage event'))
             ->_title(Mage::helper('julfiker_party')->__('Party_success_promotion'));
        if ($partysuccesspromotion->getId()) {
            $this->_title($partysuccesspromotion->getPromoCode());
        } else {
            $this->_title(Mage::helper('julfiker_party')->__('Add party_success_promotion'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new party_success_promotion action
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
     * save party_success_promotion - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('partysuccesspromotion')) {
            try {
                $partysuccesspromotion = $this->_initPartysuccesspromotion();
                $partysuccesspromotion->addData($data);
                $partysuccesspromotion->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('julfiker_party')->__('Party_success_promotion was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $partysuccesspromotion->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPartysuccesspromotionData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was a problem saving the party_success_promotion.')
                );
                Mage::getSingleton('adminhtml/session')->setPartysuccesspromotionData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('julfiker_party')->__('Unable to find party_success_promotion to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete party_success_promotion - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $partysuccesspromotion = Mage::getModel('julfiker_party/partysuccesspromotion');
                $partysuccesspromotion->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('julfiker_party')->__('Party_success_promotion was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error deleting party_success_promotion.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('julfiker_party')->__('Could not find party_success_promotion to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete party_success_promotion - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function massDeleteAction()
    {
        $partysuccesspromotionIds = $this->getRequest()->getParam('partysuccesspromotion');
        if (!is_array($partysuccesspromotionIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('julfiker_party')->__('Please select party_success_promotion to delete.')
            );
        } else {
            try {
                foreach ($partysuccesspromotionIds as $partysuccesspromotionId) {
                    $partysuccesspromotion = Mage::getModel('julfiker_party/partysuccesspromotion');
                    $partysuccesspromotion->setId($partysuccesspromotionId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('julfiker_party')->__('Total of %d party_success_promotion were successfully deleted.', count($partysuccesspromotionIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error deleting party_success_promotion.')
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
        $partysuccesspromotionIds = $this->getRequest()->getParam('partysuccesspromotion');
        if (!is_array($partysuccesspromotionIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('julfiker_party')->__('Please select party_success_promotion.')
            );
        } else {
            try {
                foreach ($partysuccesspromotionIds as $partysuccesspromotionId) {
                $partysuccesspromotion = Mage::getSingleton('julfiker_party/partysuccesspromotion')->load($partysuccesspromotionId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d party_success_promotion were successfully updated.', count($partysuccesspromotionIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error updating party_success_promotion.')
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
        $partysuccesspromotionIds = $this->getRequest()->getParam('partysuccesspromotion');
        if (!is_array($partysuccesspromotionIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('julfiker_party')->__('Please select party_success_promotion.')
            );
        } else {
            try {
                foreach ($partysuccesspromotionIds as $partysuccesspromotionId) {
                $partysuccesspromotion = Mage::getSingleton('julfiker_party/partysuccesspromotion')->load($partysuccesspromotionId)
                    ->setEventId($this->getRequest()->getParam('flag_event_id'))
                    ->setIsMassupdate(true)
                    ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d party_success_promotion were successfully updated.', count($partysuccesspromotionIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error updating party_success_promotion.')
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
        $fileName   = 'partysuccesspromotion.csv';
        $content    = $this->getLayout()->createBlock('julfiker_party/adminhtml_partysuccesspromotion_grid')
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
        $fileName   = 'partysuccesspromotion.xls';
        $content    = $this->getLayout()->createBlock('julfiker_party/adminhtml_partysuccesspromotion_grid')
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
        $fileName   = 'partysuccesspromotion.xml';
        $content    = $this->getLayout()->createBlock('julfiker_party/adminhtml_partysuccesspromotion_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('julfiker_party/partysuccesspromotion');
    }
}
