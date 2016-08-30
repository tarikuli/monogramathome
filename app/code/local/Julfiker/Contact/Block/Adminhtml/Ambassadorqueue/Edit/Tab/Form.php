<?php
/**
 * Julfiker_Contact extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Julfiker
 * @package        Julfiker_Contact
 * @copyright      Copyright (c) 2016
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Contact edit form tab
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author      Ultimate Module Creator
 */
class Julfiker_Contact_Block_Adminhtml_Ambassadorqueue_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Julfiker_Contact_Block_Adminhtml_Contact_Edit_Tab_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('contact_');
        $form->setFieldNameSuffix('contact');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'ambassadorqueue_form',
            array('legend' => Mage::helper('julfiker_contact')->__('Queue'))
        );

        $fieldset->addField(
            'domain_id',
            'text',
            array(
                'label' => Mage::helper('julfiker_contact')->__('Domain name'),
                'name'  => 'domain_id',
                'required'  => true,
                'class' => 'required-entry',
           )
        );



        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => Mage::helper('julfiker_contact')->__('Status'),
                'name'   => 'status',
                'class' => 'required-entry',
                'values'=> array(
                    '1' => Mage::helper('julfiker_contact')->__('Pending'),
                    '2' => Mage::helper('julfiker_contact')->__('Done'),
                 )
            )
        );

        $formValues = Mage::registry('current_ambassadorqueue')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getContactData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getContactData());
            Mage::getSingleton('adminhtml/session')->setContactData(null);
        } elseif (Mage::registry('current_contact')) {
            $formValues = array_merge($formValues, Mage::registry('current_ambassadorqueue')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
