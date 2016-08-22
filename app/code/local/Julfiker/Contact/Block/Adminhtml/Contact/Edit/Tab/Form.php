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
class Julfiker_Contact_Block_Adminhtml_Contact_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
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
            'contact_form',
            array('legend' => Mage::helper('julfiker_contact')->__('Contact'))
        );

        $fieldset->addField(
            'first_name',
            'text',
            array(
                'label' => Mage::helper('julfiker_contact')->__('First Name'),
                'name'  => 'first_name',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'last_name',
            'text',
            array(
                'label' => Mage::helper('julfiker_contact')->__('Last Name'),
                'name'  => 'last_name',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'email',
            'text',
            array(
                'label' => Mage::helper('julfiker_contact')->__('Email'),
                'name'  => 'email',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'phone',
            'text',
            array(
                'label' => Mage::helper('julfiker_contact')->__('Phone'),
                'name'  => 'phone',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'zipcode',
            'text',
            array(
                'label' => Mage::helper('julfiker_contact')->__('Zip code'),
                'name'  => 'zipcode',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'contact_type',
            'select',
            array(
                'label'  => Mage::helper('julfiker_contact')->__('Contact type'),
                'name'   => 'contact_type',
                'class' => 'required-entry',
                'values'=> Mage::getModel('julfiker_contact/contact_attribute_source_contacttype')->getAllOptions(true),
            )
        );

        $fieldset->addField(
            'note',
            'textarea',
            array(
                'label' => Mage::helper('julfiker_contact')->__('Note'),
                'name'  => 'note',
                'required'  => false,
                'class' => '',

           )
        );


        $fieldset->addField(
            'internal_note',
            'textarea',
            array(
                'label' => Mage::helper('julfiker_contact')->__('Customer Service Internal Notes'),
                'name'  => 'internal_note',
                'required'  => false,
                'class' => '',

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
                    '1' => Mage::helper('julfiker_contact')->__('Pending - LDM'),
                    '2' => Mage::helper('julfiker_contact')->__('Pending - Email'),
                    '3' => Mage::helper('julfiker_contact')->__('Scheduled Party'),
                    '4' => Mage::helper('julfiker_contact')->__('Recruited'),

                )
            )
        );

        $formValues = Mage::registry('current_contact')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getContactData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getContactData());
            Mage::getSingleton('adminhtml/session')->setContactData(null);
        } elseif (Mage::registry('current_contact')) {
            $formValues = array_merge($formValues, Mage::registry('current_contact')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
