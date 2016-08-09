<?php
/**
 * Julfiker_Contact extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Julfiker
 * @package        Julfiker_Contact
 * @copyright      Copyright (c) 2016
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Contact front contrller
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author      Ultimate Module Creator
 */
class Julfiker_Contact_SaveController extends Mage_Core_Controller_Front_Action
{
    /**
     * init the contact
     *
     * @access protected
     * @return Julfiker_Contact_Model_Contact
     */
    protected function _initContact()
    {
        $contact    = Mage::getModel('julfiker_contact/contact');
        return $contact;
    }

    public function indexAction() {
        if ($data = $this->getRequest()->getPost('contact')) {
            //$data['contact_type'] = implode(",", $data['contact_type']);
            try {
                $data = $this->_filterDates($data, array('contact_created_at'));
                $contact = $this->_initContact();
                $contact->addData($data);
                $contact->save();
                Mage::getSingleton('core/session')->addSuccess(
                    Mage::helper('julfiker_contact')->__('Thank you for your interest in Monogram at Home. A representative will contact you within 1 business day.')
                );
                Mage::getSingleton('core/session')->setFormData(false);
                $this->_redirect('contacts/index');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('core/session')->addError($e->getMessage());
                Mage::getSingleton('core/session')->setContactData($data);
                $this->_redirect('contacts/index');
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('core/session')->addError(
                    Mage::helper('julfiker_contact')->__('There was a problem saving the contact.')
                );
                Mage::getSingleton('core/session')->setContactData($data);
                $this->_redirect('contacts/index');
                return;
            }
        }

        Mage::getSingleton('core/session')->addError(
            Mage::helper('julfiker_contact')->__('Unable to find contact to save.')
        );
        $this->_redirect('contacts/index');
    }
}