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
 * Party order item admin edit form
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Block_Adminhtml_Partyorderitem_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
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
        parent::__construct();
        $this->_blockGroup = 'julfiker_party';
        $this->_controller = 'adminhtml_partyorderitem';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('julfiker_party')->__('Save Party order item')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('julfiker_party')->__('Delete Party order item')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('julfiker_party')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ),
            -100
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * get the edit form header
     *
     * @access public
     * @return string
     * @author Julfiker
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_partyorderitem') && Mage::registry('current_partyorderitem')->getId()) {
            return Mage::helper('julfiker_party')->__(
                "Edit Party order item '%s'",
                $this->escapeHtml(Mage::registry('current_partyorderitem')->getQty())
            );
        } else {
            return Mage::helper('julfiker_party')->__('Add Party order item');
        }
    }
}
