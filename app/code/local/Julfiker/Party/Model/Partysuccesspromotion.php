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
 * Party_success_promotion model
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Model_Partysuccesspromotion extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'party_partysuccesspromotion';
    const CACHE_TAG = 'party_partysuccesspromotion';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'party_partysuccesspromotion';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'partysuccesspromotion';

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
        $this->_init('julfiker_party/partysuccesspromotion');
    }

    /**
     * before save party_success_promotion
     *
     * @access protected
     * @return Julfiker_Party_Model_Partysuccesspromotion
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
     * save party_success_promotion relation
     *
     * @access public
     * @return Julfiker_Party_Model_Partysuccesspromotion
     * @author Julfiker
     */
    protected function _afterSave()
    {
        return parent::_afterSave();
    }

    /**
     * Retrieve parent 
     *
     * @access public
     * @return null|Julfiker_Party_Model_Event
     * @author Julfiker
     */
    public function getParentEvent()
    {
        if (!$this->hasData('_parent_event')) {
            if (!$this->getEventId()) {
                return null;
            } else {
                $event = Mage::getModel('julfiker_party/event')
                    ->load($this->getEventId());
                if ($event->getId()) {
                    $this->setData('_parent_event', $event);
                } else {
                    $this->setData('_parent_event', null);
                }
            }
        }
        return $this->getData('_parent_event');
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
