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

    const XML_PATH_EMAIL_SENDER     = 'contacts/email/sender_email_identity';
    const XML_PATH_WELCOME_TEMPLATE   = 'contacts/email/welcome_template';
    const XML_PATH_ENABLED          = 'contacts/contacts/enabled';

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
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            //$data['contact_type'] = implode(",", $data['contact_type']);
            try {
                $data = $this->_filterDates($data, array('contact_created_at'));

                //die(Mage::getStoreConfig(self::XML_PATH_WELCOME_TEMPLATE));

                try {
                    //Welcome email to submission customer
                    /* @var $mailTemplate Mage_Core_Model_Email_Template */
                    $mailTemplate = Mage::getModel('core/email_template');
                    $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                        ->setReplyTo(Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER))
                        ->sendTransactional(
                            Mage::getStoreConfig(self::XML_PATH_WELCOME_TEMPLATE),
                            Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                            Mage::getStoreConfig($data['email']),
                            null,
                            array('data' => $data)
                        );

                    if (!$mailTemplate->getSentSuccess()) {
                        Mage::getSingleton('core/session')->addError("Something went wrong! Please contact administration.");
                        $this->_redirect('contacts/index');
                        return;
                    }

                    $translate->setTranslateInline(true);
                }
                catch (Exception $e) {
                    print_r($e->getTraceAsString());
                    return;
                }


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
