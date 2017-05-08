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
 * Party_participate edit form tab
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Block_Adminhtml_Partyparticipate_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Julfiker_Party_Block_Adminhtml_Partyparticipate_Edit_Tab_Form
     * @author Julfiker
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('partyparticipate_');
        $form->setFieldNameSuffix('partyparticipate');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'partyparticipate_form',
            array('legend' => Mage::helper('julfiker_party')->__('Party_participate'))
        );
        $values = Mage::getResourceModel('julfiker_party/event_collection')
            ->toOptionArray();
        array_unshift($values, array('label' => '', 'value' => ''));

        $html = '<a href="{#url}" id="partyparticipate_event_id_link" target="_blank"></a>';
        $html .= '<script type="text/javascript">
            function changeEventIdLink() {
                if ($(\'partyparticipate_event_id\').value == \'\') {
                    $(\'partyparticipate_event_id_link\').hide();
                } else {
                    $(\'partyparticipate_event_id_link\').show();
                    var url = \''.$this->getUrl('adminhtml/party_event/edit', array('id'=>'{#id}', 'clear'=>1)).'\';
                    var text = \''.Mage::helper('core')->escapeHtml($this->__('View {#name}')).'\';
                    var realUrl = url.replace(\'{#id}\', $(\'partyparticipate_event_id\').value);
                    $(\'partyparticipate_event_id_link\').href = realUrl;
                    $(\'partyparticipate_event_id_link\').innerHTML = text.replace(\'{#name}\', $(\'partyparticipate_event_id\').options[$(\'partyparticipate_event_id\').selectedIndex].innerHTML);
                }
            }
            $(\'partyparticipate_event_id\').observe(\'change\', changeEventIdLink);
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
            'guest',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('Guest'),
                'name'  => 'guest',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'status',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('Status'),
                'name'  => 'status',
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
        $formValues = Mage::registry('current_partyparticipate')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getPartyparticipateData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getPartyparticipateData());
            Mage::getSingleton('adminhtml/session')->setPartyparticipateData(null);
        } elseif (Mage::registry('current_partyparticipate')) {
            $formValues = array_merge($formValues, Mage::registry('current_partyparticipate')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
