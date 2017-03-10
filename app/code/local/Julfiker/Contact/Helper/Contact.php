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
 * Contact helper
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author      Ultimate Module Creator
 */
class Julfiker_Contact_Helper_Contact extends Mage_Core_Helper_Abstract
{

    /**
     * get the url to the contacts list page
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getContactsUrl()
    {
        if ($listKey = Mage::getStoreConfig('julfiker_contact/contact/url_rewrite_list')) {
            return Mage::getUrl('', array('_direct'=>$listKey));
        }
        return Mage::getUrl('julfiker_contact/contact/index');
    }

    /**
     * check if breadcrumbs can be used
     *
     * @access public
     * @return bool
     * @author Ultimate Module Creator
     */
    public function getUseBreadcrumbs()
    {
        return Mage::getStoreConfigFlag('julfiker_contact/contact/breadcrumbs');
    }

    /**
     * check if the rss for contact is enabled
     *
     * @access public
     * @return bool
     * @author Ultimate Module Creator
     */
    public function isRssEnabled()
    {
        return  Mage::getStoreConfigFlag('rss/config/active') &&
            Mage::getStoreConfigFlag('julfiker_contact/contact/rss');
    }

    /**
     * get the link to the contact rss list
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getRssUrl()
    {
        return Mage::getUrl('julfiker_contact/contact/rss');
    }

    public function sendNotification($subject, $content) {

        $toMail = Mage::getStoreConfig('trans_email/ident_support/email');
        $mail = Mage::getModel('core/email');
        $mail->setToName('Customer Support');
        $mail->setToEmail($toMail);
        $mail->setBody($content);
        $mail->setSubject($subject);
        $mail->setFromEmail('no-reply@monogramathome.com');
        $mail->setFromName("Auto Notification");
        $mail->setType('text');

        try {
            $mail->send();
        }
        catch (Exception $e) {
            //Todo: add log with exception
            //die($e->getMessage());
        }
    }


    public function sendCustomerNotification($customerId, $isAmbassador = false) {

        $customer = Mage::getModel('customer/customer')->load($customerId);
        $website = Mage::getModel('core/website')->load($customer->getWebsiteId());
        $address = $customer->getDefaultBillingAddress();
        $country = Mage::getModel('directory/country')->loadByCode($address->getCountry());
        if (!$isAmbassador) {
            $subject = "Notification - Customer registration";
            $content = "Hi, \n Here is the customer information based on recent customer registration \n\n";
        }
        else {
            $subject = "Notification - New Ambassador";
            $content = "Hi, \n Here is the Ambassador information based on recent Ambassador registration \n\n";
        }
        $content .= "General information: \n";
        $content .= "Name: " .$customer->getName(). "\n";
        $content .= "Username: " .$customer->getUsername(). "\n";
        $content .= "Email: " .$customer->getEmail(). "\n";
        $content .= "Username: " .$customer->getUsername(). "\n";
        $content .= "Group: " . Mage::getModel('customer/group')->load($customer->getGroupId())->getCustomerGroupCode(). "\n";
        $content .= 'website:' . $website->getName(). "\n";
        $content .= "\nDefault address information:"."\n";
        $content .= 'company:' . $address->getCompany(). "\n";
        $content .= 'zip: ' . $address->getPostcode(). "\n";
        $content .= 'city: '. $address->getCity(). "\n";
        $street = $address->getStreet(). "\n";
        $content .= 'street: '. $street[0]. "\n";
        $content .= 'telephone:' . $address->getTelephone(). "\n";
        $content .= 'fax: ' . $address->getFax(). "\n";
        $content .= 'country:' . $country->getName(). "\n";
        $content .= "\n...,\n Auto notification";

        $this->sendNotification($subject, $content);
    }
}
