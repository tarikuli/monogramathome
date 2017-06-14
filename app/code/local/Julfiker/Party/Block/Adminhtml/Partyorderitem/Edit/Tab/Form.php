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
 * Party order item edit form tab
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Block_Adminhtml_Partyorderitem_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Julfiker_Party_Block_Adminhtml_Partyorderitem_Edit_Tab_Form
     * @author Julfiker
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('partyorderitem_');
        $form->setFieldNameSuffix('partyorderitem');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'partyorderitem_form',
            array('legend' => Mage::helper('julfiker_party')->__('Party order item'))
        );
        $values = Mage::getResourceModel('julfiker_party/event_collection')
            ->toOptionArray();
        array_unshift($values, array('label' => '', 'value' => ''));

        $html = '<a href="{#url}" id="partyorderitem_event_id_link" target="_blank"></a>';
        $html .= '<script type="text/javascript">
            function changeEventIdLink() {
                if ($(\'partyorderitem_event_id\').value == \'\') {
                    $(\'partyorderitem_event_id_link\').hide();
                } else {
                    $(\'partyorderitem_event_id_link\').show();
                    var url = \''.$this->getUrl('adminhtml/party_event/edit', array('id'=>'{#id}', 'clear'=>1)).'\';
                    var text = \''.Mage::helper('core')->escapeHtml($this->__('View {#name}')).'\';
                    var realUrl = url.replace(\'{#id}\', $(\'partyorderitem_event_id\').value);
                    $(\'partyorderitem_event_id_link\').href = realUrl;
                    $(\'partyorderitem_event_id_link\').innerHTML = text.replace(\'{#name}\', $(\'partyorderitem_event_id\').options[$(\'partyorderitem_event_id\').selectedIndex].innerHTML);
                }
            }
            $(\'partyorderitem_event_id\').observe(\'change\', changeEventIdLink);
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
            'order_id',
            'text',
            array(
                'label' => Mage::helper('julfiker_party')->__('Order Id'),
                'name'  => 'order_id',
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
        if (Mage::app()->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                array(
                    'name'      => 'stores[]',
                    'value'     => Mage::app()->getStore(true)->getId()
                )
            );
            Mage::registry('current_partyorderitem')->setStoreId(Mage::app()->getStore(true)->getId());
        }
        $formValues = Mage::registry('current_partyorderitem')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getPartyorderitemData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getPartyorderitemData());
            Mage::getSingleton('adminhtml/session')->setPartyorderitemData(null);
        } elseif (Mage::registry('current_partyorderitem')) {
            $formValues = array_merge($formValues, Mage::registry('current_partyorderitem')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
