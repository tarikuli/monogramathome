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
 * Event edit form tab
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Block_Adminhtml_Event_Edit_Tab_Coupon extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Julfiker_Party_Block_Adminhtml_Event_Edit_Tab_Coupon
     * @author Julfiker
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('event');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'event_coupon_form',
            array('legend' => Mage::helper('julfiker_party')->__('Coupon OR Voucher'))
        );

        $fieldset->addField(
            'target_one',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('Target-1 order amount'),
                'name'  => 'target_one'
            )
        );

        $fieldset->addField(
            'target_one_coupon',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('Target-1 voucher or coupon'),
                'name'  => 'target_one_coupon'
            )
        );

        $fieldset->addField(
            'target_two',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('Target-2 order amount'),
                'name'  => 'target_two'
            )
        );

        $fieldset->addField(
            'target_two_coupon',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('Target-2 voucher or coupon'),
                'name'  => 'target_two_coupon'
            )
        );

        $fieldset->addField(
            'target_three',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('Target-3 order amount'),
                'name'  => 'target_three'
            )
        );

       $field = $fieldset->addField(
            'target_three_coupon',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('Target-3 voucher or coupon'),
                'name'  => 'target_three_coupon'
            )
        );


        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);
        $form->addValues(Mage::registry('current_event')->getData());
        return parent::_prepareForm();
    }
}
