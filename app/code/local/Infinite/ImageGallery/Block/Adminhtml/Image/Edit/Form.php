<?php

class Infinite_ImageGallery_Block_Adminhtml_Image_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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

        $fieldset = $form->addFieldset('image_form', array('legend' => Mage::helper('imagegallery')->__('General Information')));

        if (Mage::getSingleton('adminhtml/session')->getFormData())
        {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } 
        elseif (Mage::registry('image_data')) 
        {
            $data = Mage::registry('image_data')->getData();
        }

        $fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('imagegallery')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
        ));

        $fieldset->addField('description', 'textarea', array(
            'label'     => Mage::helper('imagegallery')->__('Description'),
            'name'      => 'description',
        ));

        $fieldset->addField('image', 'image', array(
            'label' => Mage::helper('imagegallery')->__('Image'),
            'required' => false,
            'name' => 'image',
        ));

        $fieldset->addField('image_row', 'text', array(
            'label'     => Mage::helper('imagegallery')->__('Row Number'),
            'class'     => 'required-entry validate-number',
            'required'  => true,
            'name'      => 'image_row',
        ));

        $fieldset->addField('image_order', 'text', array(
            'label'     => Mage::helper('imagegallery')->__('Order'),
            'class'     => 'required-entry validate-number',
            'required'  => true,
            'name'      => 'image_order',
        ));

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('imagegallery')->__('Status'),
            'name'      => 'is_active',
            'values'    => Mage::getSingleton('imagegallery/image')->getStatusArray(),
        ));        
        
        $form->setValues($data);

        return parent::_prepareForm();
    }
}