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
 * Party_success_promotion edit form tab
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Block_Adminhtml_Partysuccesspromotion_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Julfiker_Party_Block_Adminhtml_Partysuccesspromotion_Edit_Tab_Form
     * @author Julfiker
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('partysuccesspromotion_');
        $form->setFieldNameSuffix('partysuccesspromotion');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'partysuccesspromotion_form',
            array('legend' => Mage::helper('julfiker_party')->__('Party_success_promotion'))
        );
        $values = Mage::getResourceModel('julfiker_party/event_collection')
            ->toOptionArray();
        array_unshift($values, array('label' => '', 'value' => ''));

        $html = '<a href="{#url}" id="partysuccesspromotion_event_id_link" target="_blank"></a>';
        $html .= '<script type="text/javascript">
            function changeEventIdLink() {
                if ($(\'partysuccesspromotion_event_id\').value == \'\') {
                    $(\'partysuccesspromotion_event_id_link\').hide();
                } else {
                    $(\'partysuccesspromotion_event_id_link\').show();
                    var url = \''.$this->getUrl('adminhtml/party_event/edit', array('id'=>'{#id}', 'clear'=>1)).'\';
                    var text = \''.Mage::helper('core')->escapeHtml($this->__('View {#name}')).'\';
                    var realUrl = url.replace(\'{#id}\', $(\'partysuccesspromotion_event_id\').value);
                    $(\'partysuccesspromotion_event_id_link\').href = realUrl;
                    $(\'partysuccesspromotion_event_id_link\').innerHTML = text.replace(\'{#name}\', $(\'partysuccesspromotion_event_id\').options[$(\'partysuccesspromotion_event_id\').selectedIndex].innerHTML);
                }
            }
            $(\'partysuccesspromotion_event_id\').observe(\'change\', changeEventIdLink);
            changeEventIdLink();
            </script>';

        $fieldset->addField(
            'event_id',
            'select',
            array(
                'label'     => Mage::helper('julfiker_party')->__('Event'),
                'name'      => 'event_id',
                'required'  => false,
                'values'    => $values,
                'after_element_html' => $html
            )
        );

        $fieldset->addField(
            'promo_code',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('promo code'),
                'name'  => 'promo_code',
                'required'  => true,
                'class' => 'required-entry',

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
        $formValues = Mage::registry('current_partysuccesspromotion')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getPartysuccesspromotionData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getPartysuccesspromotionData());
            Mage::getSingleton('adminhtml/session')->setPartysuccesspromotionData(null);
        } elseif (Mage::registry('current_partysuccesspromotion')) {
            $formValues = array_merge($formValues, Mage::registry('current_partysuccesspromotion')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
