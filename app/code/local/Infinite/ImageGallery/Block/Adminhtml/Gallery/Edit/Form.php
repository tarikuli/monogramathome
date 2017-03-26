<?php

class Infinite_ImageGallery_Block_Adminhtml_Gallery_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);

        $this->setForm($form);

        $fieldset = $form->addFieldset('gallery_form', array('legend' => Mage::helper('imagegallery')->__('General Information')));

        if (Mage::getSingleton('adminhtml/session')->getFormData())
        {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } 
        elseif (Mage::registry('gallery_data')) 
        {
            $data = Mage::registry('gallery_data')->getData();
        }

        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('imagegallery')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));

        $fieldset->addField('identifier', 'text', array(
            'label'     => Mage::helper('imagegallery')->__('Identifier'),
            'class'     => 'required-entry validate-identifier',
            'required'  => true,
            'name'      => 'identifier',
        ));

        /* $fieldset->addField('no_of_images_per_row', 'text', array(
            'label'     => Mage::helper('imagegallery')->__('No of Images Per Row'),
            'class'     => 'required-entry validate-number',
            'required'  => true,
            'name'      => 'no_of_images_per_row',
        )); */

        $fieldset->addField('height_of_icon', 'text', array(
            'label'     => Mage::helper('imagegallery')->__('Height of Icons'),
            'class'     => 'required-entry validate-number',
            'required'  => true,
            'name'      => 'height_of_icon',
            'after_element_html' => '<p>Set value in pixels, this will common for all the rows of gallery.</p>'
        ));

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('imagegallery')->__('Status'),
            'name'      => 'is_active',
            'values'    => Mage::getSingleton('imagegallery/gallery')->getStatusArray(),
        ));        
        
        $form->setValues($data);

        return parent::_prepareForm();
    }
}