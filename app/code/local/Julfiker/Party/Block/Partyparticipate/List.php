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
 * Party_participate list block
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author Julfiker
 */
class Julfiker_Party_Block_Partyparticipate_List extends Mage_Core_Block_Template
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
        $partyparticipates = Mage::getResourceModel('julfiker_party/partyparticipate_collection');
        //$partyparticipates->setOrder('entity_id', 'asc');
        $this->setPartyparticipates($partyparticipates);
    }

    /**
     * prepare the layout
     *
     * @access protected
     * @return Julfiker_Party_Block_Partyparticipate_List
     * @author Julfiker
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock(
            'page/html_pager',
            'julfiker_party.partyparticipate.html.pager'
        )
        ->setCollection($this->getPartyparticipates());
        $this->setChild('pager', $pager);
        $this->getPartyparticipates()->load();
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
