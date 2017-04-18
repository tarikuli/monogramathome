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
class Julfiker_Party_Model_Adminhtml_Search_Partyorderitem extends Varien_Object
{
    /**
     * Load search results
     *
     * @access public
     * @return Julfiker_Party_Model_Adminhtml_Search_Partyorderitem
     * @author Julfiker
     */
    public function load()
    {
        $arr = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        $collection = Mage::getResourceModel('julfiker_party/partyorderitem_collection')
            ->addFieldToFilter('qty', array('like' => $this->getQuery().'%'))
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->load();
        foreach ($collection->getItems() as $partyorderitem) {
            $arr[] = array(
                'id'          => 'partyorderitem/1/'.$partyorderitem->getId(),
                'type'        => Mage::helper('julfiker_party')->__('Party order item'),
                'name'        => $partyorderitem->getQty(),
                'description' => $partyorderitem->getQty(),
                'url' => Mage::helper('adminhtml')->getUrl(
                    '*/party_partyorderitem/edit',
                    array('id'=>$partyorderitem->getId())
                ),
            );
        }
        $this->setResults($arr);
        return $this;
    }
}
