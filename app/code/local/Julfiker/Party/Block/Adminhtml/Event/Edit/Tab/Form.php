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
class Julfiker_Party_Block_Adminhtml_Event_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Julfiker_Party_Block_Adminhtml_Event_Edit_Tab_Form
     * @author Julfiker
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('event_');
        $form->setFieldNameSuffix('event');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'event_form',
            array('legend' => Mage::helper('julfiker_party')->__('Event'))
        );

        $fieldset->addField(
            'title',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('Title'),
                'name'  => 'title',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'desc',
            'textarea',
            array(
                'label' => Mage::helper('julfiker_party')->__('Description'),
                'name'  => 'desc',

           )
        );

        $fieldset->addField(
            'start_at',
            'date',
            array(
                'label' => Mage::helper('julfiker_party')->__('Start time'),
                'name'  => 'start_at',
                'required'  => true,
                'class' => 'required-entry',

            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format'  => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
           )
        );

        $fieldset->addField(
            'end_at',
            'date',
            array(
                'label' => Mage::helper('julfiker_party')->__('End time'),
                'name'  => 'end_at',
                'required'  => true,
                'class' => 'required-entry',

            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format'  => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
           )
        );

        $fieldset->addField(
            'country',
            'select',
            array(
                'label' => Mage::helper('julfiker_party')->__('Country'),
                'name'  => 'country',
                'required'  => true,
                'class' => 'required-entry',

            'values'=> Mage::getResourceModel('directory/country_collection')->toOptionArray(),
           )
        );

        $fieldset->addField(
            'city',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('City'),
                'name'  => 'city',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'zip',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('Zip'),
                'name'  => 'zip',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'address',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('Address'),
                'name'  => 'address',
                'required'  => true,
                'class' => 'required-entry',

           )
        );
        $fieldset->addField(
            'url_key',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('Url key'),
                'name'  => 'url_key',
                'note'  => Mage::helper('julfiker_party')->__('Relative to Website Base URL')
            )
        );
        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => Mage::helper('julfiker_party')->__('Status'),
                'name'   => 'status',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('julfiker_party')->__('Enabled'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('julfiker_party')->__('Disabled'),
                    ),
                ),
            )
        );
        if (Mage::app()->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                array(
                    'name'      => 'stores[]',
                    'value'     => Mage::app()->getStore(true)->getId()
                )
            );
            Mage::registry('current_event')->setStoreId(Mage::app()->getStore(true)->getId());
        }
        $formValues = Mage::registry('current_event')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getEventData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getEventData());
            Mage::getSingleton('adminhtml/session')->setEventData(null);
        } elseif (Mage::registry('current_event')) {
            $formValues = array_merge($formValues, Mage::registry('current_event')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
