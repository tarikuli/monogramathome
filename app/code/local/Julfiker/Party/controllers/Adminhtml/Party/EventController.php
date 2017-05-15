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
 * Event admin controller
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Adminhtml_Party_EventController extends Julfiker_Party_Controller_Adminhtml_Party
{
    /**
     * init the event
     *
     * @access protected
     * @return Julfiker_Party_Model_Event
     */
    protected function _initEvent()
    {
        $eventId  = (int) $this->getRequest()->getParam('id');
        $event    = Mage::getModel('julfiker_party/event');
        if ($eventId) {
            $event->load($eventId);
        }
        Mage::register('current_event', $event);
        return $event;
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
             ->_title(Mage::helper('julfiker_party')->__('Events'));
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
     * edit event - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function editAction()
    {
        $eventId    = $this->getRequest()->getParam('id');
        $event      = $this->_initEvent();
        if ($eventId && !$event->getId()) {
            $this->_getSession()->addError(
                Mage::helper('julfiker_party')->__('This event no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getEventData(true);
        if (!empty($data)) {
            $event->setData($data);
        }
        Mage::register('event_data', $event);
        $this->loadLayout();
        $this->_title(Mage::helper('julfiker_party')->__('Manage event'))
             ->_title(Mage::helper('julfiker_party')->__('Events'));
        if ($event->getId()) {
            $this->_title($event->getZip());
        } else {
            $this->_title(Mage::helper('julfiker_party')->__('Add event'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new event action
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
     * save event - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('event')) {

            try {
                $data = $this->_filterDateTime($data, array('start_at' ,'end_at'));
                $event = $this->_initEvent();
                $event->addData($data);
                $event->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('julfiker_party')->__('Event was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $event->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setEventData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was a problem saving the event.')
                );
                Mage::getSingleton('adminhtml/session')->setEventData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('julfiker_party')->__('Unable to find event to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete event - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $event = Mage::getModel('julfiker_party/event');
                $event->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('julfiker_party')->__('Event was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error deleting event.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('julfiker_party')->__('Could not find event to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete event - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function massDeleteAction()
    {
        $eventIds = $this->getRequest()->getParam('event');
        if (!is_array($eventIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('julfiker_party')->__('Please select events to delete.')
            );
        } else {
            try {
                foreach ($eventIds as $eventId) {
                    $event = Mage::getModel('julfiker_party/event');
                    $event->setId($eventId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('julfiker_party')->__('Total of %d events were successfully deleted.', count($eventIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error deleting events.')
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
        $eventIds = $this->getRequest()->getParam('event');
        if (!is_array($eventIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('julfiker_party')->__('Please select events.')
            );
        } else {
            try {
                foreach ($eventIds as $eventId) {
                $event = Mage::getSingleton('julfiker_party/event')->load($eventId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d events were successfully updated.', count($eventIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error updating events.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass Country change - action
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function massCountryAction()
    {
        $eventIds = $this->getRequest()->getParam('event');
        if (!is_array($eventIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('julfiker_party')->__('Please select events.')
            );
        } else {
            try {
                foreach ($eventIds as $eventId) {
                $event = Mage::getSingleton('julfiker_party/event')->load($eventId)
                    ->setCountry($this->getRequest()->getParam('flag_country'))
                    ->setIsMassupdate(true)
                    ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d events were successfully updated.', count($eventIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('julfiker_party')->__('There was an error updating events.')
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
        $fileName   = 'event.csv';
        $content    = $this->getLayout()->createBlock('julfiker_party/adminhtml_event_grid')
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
        $fileName   = 'event.xls';
        $content    = $this->getLayout()->createBlock('julfiker_party/adminhtml_event_grid')
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
        $fileName   = 'event.xml';
        $content    = $this->getLayout()->createBlock('julfiker_party/adminhtml_event_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('julfiker_party/event');
    }
}
