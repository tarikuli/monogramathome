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
 * Sender helper
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Helper_Sender extends Mage_Core_Helper_Abstract
{

    /**
     * Sending invitation email
     *
     * @param $email
     * @param $data
     */
    public function sendInviteEmail($email, $data) {
        $template = Mage::getStoreConfig("julfiker_party/email/invite_template");
        $mailTemplate = Mage::getModel('core/email_template');
        $mailTemplate->setDesignConfig(array('area' => 'frontend'))
            //->setReplyTo(Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER))
            //->addBcc('support@monogramathome.com ')
            ->sendTransactional(
                $template,
                Mage::getStoreConfig('contacts/email/sender_email_identity'),
                $email,
                null,
                $data
            );
    }

    /**
     * Email content
     *
     * @param $eventId
     * @return array
     */
    public function getEmailData($eventId) {
        $event = Mage::getModel('julfiker_party/event')->load($eventId);
        $member = Mage::getSingleton('customer/customer')->load($event->getHost());
        $data = array();
        $data['location'] = $event->getAddress().", ".$event->getCity().", ".$event->getZip().", ".$event->getCountry();
        $data['title'] = $event->getTitle();
        $data['start_at'] =  date("d-m-Y h:i A", strtotime($event->getStartAt()));;
        $data['end_at'] = date("d-m-Y h:i A", strtotime($event->getEndAt()));;
        $data['host'] = $member->getName();
        $data['joinUrl'] = Mage::getUrl("julfiker_party/participate/confirm");
        $data['rejectUrl'] = Mage::getUrl("julfiker_party/participate/confirm");

        return $data;
    }

    /**
     * Sending host welcome email templates
     *
     * @param $eventId
     * @return mixed
     */
    public function sendHostWelcomeEmail($eventId) {
        try {
            $event = Mage::getModel('julfiker_party/event')->load($eventId);
            $member = Mage::getSingleton('customer/customer')->load($event->getHost());
            $data = $this->getEmailData($eventId);
            $hostCredential = Mage::getSingleton('core/session')->getHostCredential();

            if ($hostCredential) {
                $data['hostEmail'] = $hostCredential['email'];
                $data['hostPassword'] = $hostCredential['password'];
            }

            $data['eventUrl'] = $event->getEventUrl();
            $data['loginUrl'] = Mage::getUrl('customer/account/login/');

            $template = Mage::getStoreConfig("julfiker_party/email/welcome_host_template");
            $mailTemplate = Mage::getModel('core/email_template');

            //Unset host credential
            Mage::getSingleton('core/session')->unsHostCredential();
            return $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                //->setReplyTo(Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER))
                //->addBcc('support@monogramathome.com ')
                ->sendTransactional(
                    $template,
                    Mage::getStoreConfig('contacts/email/sender_email_identity'),
                    $member->getEmail(),
                    null,
                    $data
                );
        }
        catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
