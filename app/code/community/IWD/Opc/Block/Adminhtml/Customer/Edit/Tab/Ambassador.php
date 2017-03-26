<?php 

class IWD_Opc_Block_Adminhtml_Customer_Edit_Tab_Ambassador extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $addressObject = Mage::getModel('opc/address');
        $addressCollection = $addressObject->getCollection()->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId());
        if($addressCollection->count())
        	$addressObject->load($addressCollection->getFirstItem()->getId());

        $fieldset = $form->addFieldset('ambassador_general_settings', array('legend'=>Mage::helper('catalog')->__('Ambassador Address')));

        $addresses = array('', '');
        if($addressObject->getId())
        	$addresses = json_decode($addressObject->getAddress(), true);

        $i = 0;
        foreach($addresses as $address)
        {
        	$args = array(	            
	            'name'  => 'general[address][]',
	            'value' => $address,
	        );
        	if($i == 0) {
        		$args['label'] = Mage::helper('catalog')->__('Address');
	            $args['title'] = Mage::helper('catalog')->__('Address');
        	}
        	$fieldset->addField('general_address_' . $i, 'text', $args);
        	unset($args);
	        $i++;
        }

        $fieldset->addField('general_city', 'text', array(
        	'label' => Mage::helper('catalog')->__('City'),
            'title' => Mage::helper('catalog')->__('City'),
        	'name'  => 'general[city]',
            'value' => $addressObject->getCity(),
    	));

    	$fieldset->addField('general_state', 'text', array(
        	'label' => Mage::helper('catalog')->__('State'),
            'title' => Mage::helper('catalog')->__('State'),
        	'name'  => 'general[state]',
            'value' => $addressObject->getState(),
    	));

    	$fieldset->addField('general_country', 'select', array(
        	'label' => Mage::helper('catalog')->__('Country'),
            'title' => Mage::helper('catalog')->__('Country'),
        	'name'  => 'general[country]',
        	'values' => Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(),
            'value' => $addressObject->getCountry(),
    	));

    	$fieldset->addField('general_zipcode', 'text', array(
        	'label' => Mage::helper('catalog')->__('Zipcode'),
            'title' => Mage::helper('catalog')->__('Zipcode'),
        	'name'  => 'general[zipcode]',
            'value' => $addressObject->getZipcode(),
    	));

    	$fieldset->addField('general_telephone', 'text', array(
        	'label' => Mage::helper('catalog')->__('Telephone'),
            'title' => Mage::helper('catalog')->__('Telephone'),
        	'name'  => 'general[telephone]',
            'value' => $addressObject->getTelephone(),
    	));

    	$fieldset->addField('general_fax', 'text', array(
        	'label' => Mage::helper('catalog')->__('Fax'),
            'title' => Mage::helper('catalog')->__('Fax'),
        	'name'  => 'general[fax]',
            'value' => $addressObject->getFax(),
    	));

        $this->setForm($form);
    }
}

?>