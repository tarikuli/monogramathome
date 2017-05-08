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
 * Party order item list block
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author Julfiker
 */
class Julfiker_Party_Block_Partyorderitem_List extends Mage_Core_Block_Template
{
    /**
     * initialize
     *
     * @access public
     * @author Julfiker
     */
    public function _construct()
    {
        parent::_construct();
        $partyorderitems = Mage::getResourceModel('julfiker_party/partyorderitem_collection')
                         ->addStoreFilter(Mage::app()->getStore())
                         ->addFieldToFilter('status', 1);
        $partyorderitems->setOrder('qty', 'asc');
        $this->setPartyorderitems($partyorderitems);
    }

    /**
     * prepare the layout
     *
     * @access protected
     * @return Julfiker_Party_Block_Partyorderitem_List
     * @author Julfiker
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock(
            'page/html_pager',
            'julfiker_party.partyorderitem.html.pager'
        )
        ->setCollection($this->getPartyorderitems());
        $this->setChild('pager', $pager);
        $this->getPartyorderitems()->load();
        return $this;
    }

    /**
     * get the pager html
     *
     * @access public
     * @return string
     * @author Julfiker
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
