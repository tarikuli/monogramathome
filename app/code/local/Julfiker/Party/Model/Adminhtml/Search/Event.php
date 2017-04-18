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
 * Admin search model
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Model_Adminhtml_Search_Event extends Varien_Object
{
    /**
     * Load search results
     *
     * @access public
     * @return Julfiker_Party_Model_Adminhtml_Search_Event
     * @author Julfiker
     */
    public function load()
    {
        $arr = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        $collection = Mage::getResourceModel('julfiker_party/event_collection')
            ->addFieldToFilter('zip', array('like' => $this->getQuery().'%'))
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->load();
        foreach ($collection->getItems() as $event) {
            $arr[] = array(
                'id'          => 'event/1/'.$event->getId(),
                'type'        => Mage::helper('julfiker_party')->__('Event'),
                'name'        => $event->getZip(),
                'description' => $event->getZip(),
                'url' => Mage::helper('adminhtml')->getUrl(
                    '*/party_event/edit',
                    array('id'=>$event->getId())
                ),
            );
        }
        $this->setResults($arr);
        return $this;
    }
}
