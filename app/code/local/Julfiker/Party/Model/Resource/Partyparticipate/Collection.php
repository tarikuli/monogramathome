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
 * Party_participate collection resource model
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Model_Resource_Partyparticipate_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_joinedFields = array();

    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('julfiker_party/partyparticipate');
    }

    /**
     * get party_participates as array
     *
     * @access protected
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     * @author Julfiker
     */
    protected function _toOptionArray($valueField='entity_id', $labelField='status', $additional=array())
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    /**
     * get options hash
     *
     * @access protected
     * @param string $valueField
     * @param string $labelField
     * @return array
     * @author Julfiker
     */
    protected function _toOptionHash($valueField='entity_id', $labelField='status')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @access public
     * @return Varien_Db_Select
     * @author Julfiker
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Zend_Db_Select::GROUP);
        return $countSelect;
    }
}
