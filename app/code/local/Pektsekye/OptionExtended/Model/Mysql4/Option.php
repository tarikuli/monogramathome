<?php

class Pektsekye_OptionExtended_Model_Mysql4_Option extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('optionextended/option', 'ox_option_id');
    }
    
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(array(
            'field' => 'code',
            'title' => Mage::helper('optionextended')->__('Option with the same code')
        ));
        return $this;
    }

    	
	    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
		    $read = $this->_getReadAdapter();
		    $write = $this->_getWriteAdapter();
		    $noteTable = $this->getTable('optionextended/option_note');
		    		
        if (!$object->getData('scope', 'optionextended_note')) {		
		      $statement = $read->select()
			      ->from($noteTable)
			      ->where('ox_option_id = '.$object->getId().' AND store_id = ?', 0);

		      if ($read->fetchOne($statement)) {
			      if ($object->getStoreId() == '0') {
				      $write->update(
					      $noteTable,
						      array('note' => $object->getNote()),
						      $write->quoteInto('ox_option_id='.$object->getId().' AND store_id=?', 0)
				      );
			      }
		      } else {
			      $write->insert(
				      $noteTable,
					      array(
						      'ox_option_id' => $object->getId(),
						      'store_id' => 0,
						      'note' => $object->getNote()
			      ));
		      }
        }
        
		    if ($object->getStoreId() != '0' && !$object->getData('scope', 'optionextended_note')) {
			    $statement = $read->select()
				    ->from($noteTable)
				    ->where('ox_option_id = '.$object->getId().' AND store_id = ?', $object->getStoreId());

			    if ($read->fetchOne($statement)) {;
				    $write->update(
					    $noteTable,
						    array('note' => $object->getNote()),
						    $write->quoteInto('ox_option_id='.$object->getId().' AND store_id=?', $object->getStoreId()));
			    } else {
				    $write->insert(
					    $noteTable,
						    array(
							    'ox_option_id' => $object->getId(),
							    'store_id' => $object->getStoreId(),
							    'note' => $object->getNote()
				    ));
			    }
		    } elseif ($object->getData('scope', 'optionextended_note')){
            $write->delete(
                $noteTable,
                $write->quoteInto('ox_option_id = '.$object->getId().' AND store_id = ?', $object->getStoreId())
            );		    
		    }
	}
}
