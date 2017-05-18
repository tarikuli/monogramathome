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
                array('data' => $data)
            );
    }

    public function getEmailData($eventId) {
        $event = Mage::getModel('julfiker_party/event')->load($eventId);
        $member = Mage::getSingleton('customer/customer')->load($event->getHost());
        $data = array();
        $data['location'] = $event->getAddress().", ".$event->getCity().", ".$event->getZip().", ".$event->getCountry();
        $data['title'] = $event->getTitle();
        $data['start_at'] = $event->getStartAt();
        $data['end_at'] = $event->getEndAt();
        $data['host'] = $member->getName();

        return $data;
    }
}
