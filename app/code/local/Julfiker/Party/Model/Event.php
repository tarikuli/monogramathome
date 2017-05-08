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
 * Event model
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Model_Event extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'party_event';
    const CACHE_TAG = 'party_event';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'party_event';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'event';

    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('julfiker_party/event');
    }

    /**
     * before save event
     *
     * @access protected
     * @return Julfiker_Party_Model_Event
     * @author Julfiker
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        return $this;
    }

    /**
     * get the url to the event details page
     *
     * @access public
     * @return string
     * @author Julfiker
     */
    public function getEventUrl()
    {
        if ($this->getUrlKey()) {
            $urlKey = '';
            if ($prefix = Mage::getStoreConfig('julfiker_party/event/url_prefix')) {
                $urlKey .= $prefix.'/';
            }
            $urlKey .= $this->getUrlKey();
            if ($suffix = Mage::getStoreConfig('julfiker_party/event/url_suffix')) {
                $urlKey .= '.'.$suffix;
            }
            return Mage::getUrl('', array('_direct'=>$urlKey));
        }
        return Mage::getUrl('julfiker_party/event/view', array('id'=>$this->getId()));
    }

    /**
     * check URL key
     *
     * @access public
     * @param string $urlKey
     * @param bool $active
     * @return mixed
     * @author Julfiker
     */
    public function checkUrlKey($urlKey, $active = true)
    {
        return $this->_getResource()->checkUrlKey($urlKey, $active);
    }

    /**
     * save event relation
     *
     * @access public
     * @return Julfiker_Party_Model_Event
     * @author Julfiker
     */
    protected function _afterSave()
    {
        return parent::_afterSave();
    }

    /**
     * Retrieve  collection
     *
     * @access public
     * @return Julfiker_Party_Model_Partyorderitem_Collection
     * @author Julfiker
     */
    public function getSelectedPartyorderitemsCollection()
    {
        if (!$this->hasData('_partyorderitem_collection')) {
            if (!$this->getId()) {
                return new Varien_Data_Collection();
            } else {
                $collection = Mage::getResourceModel('julfiker_party/partyorderitem_collection')
                        ->addFieldToFilter('event_id', $this->getId());
                $this->setData('_partyorderitem_collection', $collection);
            }
        }
        return $this->getData('_partyorderitem_collection');
    }

    /**
     * Retrieve  collection
     *
     * @access public
     * @return Julfiker_Party_Model_Partyparticipate_Collection
     * @author Julfiker
     */
    public function getSelectedPartyparticipatesCollection()
    {
        if (!$this->hasData('_partyparticipate_collection')) {
            if (!$this->getId()) {
                return new Varien_Data_Collection();
            } else {
                $collection = Mage::getResourceModel('julfiker_party/partyparticipate_collection')
                        ->addFieldToFilter('event_id', $this->getId());
                $this->setData('_partyparticipate_collection', $collection);
            }
        }
        return $this->getData('_partyparticipate_collection');
    }

    /**
     * Retrieve  collection
     *
     * @access public
     * @return Julfiker_Party_Model_Partysuccesspromotion_Collection
     * @author Julfiker
     */
    public function getSelectedPartysuccesspromotionsCollection()
    {
        if (!$this->hasData('_partysuccesspromotion_collection')) {
            if (!$this->getId()) {
                return new Varien_Data_Collection();
            } else {
                $collection = Mage::getResourceModel('julfiker_party/partysuccesspromotion_collection')
                        ->addFieldToFilter('event_id', $this->getId());
                $this->setData('_partysuccesspromotion_collection', $collection);
            }
        }
        return $this->getData('_partysuccesspromotion_collection');
    }

    /**
     * get default values
     *
     * @access public
     * @return array
     * @author Julfiker
     */
    public function getDefaultValues()
    {
        $values = array();
        $values['status'] = 1;
        return $values;
    }
    
}
