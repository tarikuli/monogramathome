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
 * Event Party_success_promotion list block
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Block_Event_Partysuccesspromotion_List extends Julfiker_Party_Block_Partysuccesspromotion_List
{
    /**
     * initialize
     *
     * @access public
     * @author Julfiker
     */
    public function __construct()
    {
        parent::__construct();
        $event = $this->getEvent();
        if ($event) {
            $this->getPartysuccesspromotions()->addFieldToFilter('event_id', $event->getId());
        }
    }

    /**
     * prepare the layout - actually do nothing
     *
     * @access protected
     * @return Julfiker_Party_Block_Event_Partysuccesspromotion_List
     * @author Julfiker
     */
    protected function _prepareLayout()
    {
        return $this;
    }

    /**
     * get the current event
     *
     * @access public
     * @return Julfiker_Party_Model_Event
     * @author Julfiker
     */
    public function getEvent()
    {
        return Mage::registry('current_event');
    }
}
