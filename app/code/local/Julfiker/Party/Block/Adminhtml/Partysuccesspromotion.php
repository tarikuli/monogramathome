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
 * Party_success_promotion admin block
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Block_Adminhtml_Partysuccesspromotion extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Julfiker
     */
    public function __construct()
    {
        $this->_controller         = 'adminhtml_partysuccesspromotion';
        $this->_blockGroup         = 'julfiker_party';
        parent::__construct();
        $this->_headerText         = Mage::helper('julfiker_party')->__('Party_success_promotion');
        $this->_updateButton('add', 'label', Mage::helper('julfiker_party')->__('Add Party_success_promotion'));

    }
}
