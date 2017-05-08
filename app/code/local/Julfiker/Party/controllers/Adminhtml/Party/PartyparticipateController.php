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
 * Party_participate admin controller
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Adminhtml_Party_PartyparticipateController extends Julfiker_Party_Controller_Adminhtml_Party
{
    /**
     * init the party_participate
     *
     * @access protected
     * @return Julfiker_Party_Model_Partyparticipate
     */
    protected function _initPartyparticipate()
    {
        $partyparticipateId  = (int) $this->getRequest()->getParam('id');
        $partyparticipate    = Mage::getModel('julfiker_party/partyparticipate');
        if ($partyparticipateId) {
            $partyparticipate->load($partyparticipateId);
        }
        Mage::register('current_partyparticipate', $partyparticipate);
        return $partyparticipate;
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
             ->_title(Mage::helper('julfiker_party')->__('Party_participates'));
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
     * edit party_participate - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function editAction()
    {
        $partyparticipateId    = $this->getRequest()->getParam('id');
        $partyparticipate      = $this->_initPartyparticipate();
        if ($partyparticipateId && !$partyparticipate->getId()) {
            $this->_getSession()->addError(
                Mage::helper('julfiker_party')->__('This party_participate no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getPartyparticipateData(true);
        if (!empty($data)) {
            $partyparticipate->setData($data);
        }
        Mage::register('partyparticipate_data', $partyparticipate);
        $this->loadLayout();
        $this->_title(Mage::helper('julfiker_party')->__('Manage event'))
             ->_title(Mage::helper('julfiker_party')->__('Party_participates'));
        if ($partyparticipate->getId()) {
            $this->_title($partyparticipate->getStatus());
        } else {
            $this->_title(Mage::helper('julfiker_party')->__('Add party_participate'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new party_participate action
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
     * save party_participate - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('partyparticipate')) {
            try {
                $partyparticipate = $this->_initPartyparticipate();
                $partyparticipate->addData($data);
                $partyparticipate->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('julfiker_party')->__('Party_participate was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $partyparticipate->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPartyparticipateData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was a problem saving the party_participate.')
                );
                Mage::getSingleton('adminhtml/session')->setPartyparticipateData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('julfiker_party')->__('Unable to find party_participate to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete party_participate - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $partyparticipate = Mage::getModel('julfiker_party/partyparticipate');
                $partyparticipate->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('julfiker_party')->__('Party_participate was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error deleting party_participate.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('julfiker_party')->__('Could not find party_participate to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete party_participate - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function massDeleteAction()
    {
        $partyparticipateIds = $this->getRequest()->getParam('partyparticipate');
        if (!is_array($partyparticipateIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('julfiker_party')->__('Please select party_participates to delete.')
            );
        } else {
            try {
                foreach ($partyparticipateIds as $partyparticipateId) {
                    $partyparticipate = Mage::getModel('julfiker_party/partyparticipate');
                    $partyparticipate->setId($partyparticipateId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('julfiker_party')->__('Total of %d party_participates were successfully deleted.', count($partyparticipateIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error deleting party_participates.')
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
        $partyparticipateIds = $this->getRequest()->getParam('partyparticipate');
        if (!is_array($partyparticipateIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('julfiker_party')->__('Please select party_participates.')
            );
        } else {
            try {
                foreach ($partyparticipateIds as $partyparticipateId) {
                $partyparticipate = Mage::getSingleton('julfiker_party/partyparticipate')->load($partyparticipateId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d party_participates were successfully updated.', count($partyparticipateIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error updating party_participates.')
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
        $partyparticipateIds = $this->getRequest()->getParam('partyparticipate');
        if (!is_array($partyparticipateIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('julfiker_party')->__('Please select party_participates.')
            );
        } else {
            try {
                foreach ($partyparticipateIds as $partyparticipateId) {
                $partyparticipate = Mage::getSingleton('julfiker_party/partyparticipate')->load($partyparticipateId)
                    ->setEventId($this->getRequest()->getParam('flag_event_id'))
                    ->setIsMassupdate(true)
                    ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d party_participates were successfully updated.', count($partyparticipateIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error updating party_participates.')
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
        $fileName   = 'partyparticipate.csv';
        $content    = $this->getLayout()->createBlock('julfiker_party/adminhtml_partyparticipate_grid')
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
        $fileName   = 'partyparticipate.xls';
        $content    = $this->getLayout()->createBlock('julfiker_party/adminhtml_partyparticipate_grid')
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
        $fileName   = 'partyparticipate.xml';
        $content    = $this->getLayout()->createBlock('julfiker_party/adminhtml_partyparticipate_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('julfiker_party/partyparticipate');
    }
}
