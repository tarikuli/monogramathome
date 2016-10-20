<?php
/**
 * First Data TransArmor - Admin update feed
 *
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 *
 * Having a problem with the plugin?
 * Not sure what something means?
 * Need custom development?
 * Give us a call!
 *
 * @category    ParadoxLabs
 * @package     ParadoxLabs_Transarmor
 * @author      Ryan Hoerr <ryan@paradoxlabs.com>
 */

class ParadoxLabs_Transarmor_Model_Feed extends Mage_AdminNotification_Model_Feed
{
	public function getFeedUrl() {
		$protocol = Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://';
		$this->_feedUrl = $protocol . 'store.paradoxlabs.com/updates.php?key=firstdatarp';
		
		return $this->_feedUrl;
	}
	
	public function observe() {
		$this->checkUpdate();
	}
}
