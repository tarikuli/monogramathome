<?php
class Egs_Firstdata_Block_Info_Firstdata extends Mage_Payment_Block_Info
{
	protected function _prepareSpecificInformation($transport = null)
	{
		if (null !== $this->_paymentSpecificInformation) {
			return $this->_paymentSpecificInformation;
		}
		
		
		
		$info = $this->getInfo();
		$transport = new Varien_Object();
		$transport = parent::_prepareSpecificInformation($transport);
		$transport->addData(array(
			
			Mage::helper('payment')->__('Exp Date') => $info->getCcExpMonth() . ' / '.$info->getCcExpYear(),
			Mage::helper('payment')->__('name') => $info->getCcOwner(),
			Mage::helper('payment')->__('Type') => Mage::helper('firstdata')->typechance(Mage::getStoreConfig('payment/firstdata/trans_type'))
			
			
			
		));
		return $transport;
	}
}
