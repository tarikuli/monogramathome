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
 * Contact resource model
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author      Ultimate Module Creator
 */
class Julfiker_Contact_Model_Resource_Contact extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * constructor
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function _construct()
    {
        $this->_init('julfiker_contact/contact', 'entity_id');
    }

//    /**
//     * Get store ids to which specified item is assigned
//     *
//     * @access public
//     * @param int $contactId
//     * @return array
//     * @author Ultimate Module Creator
//     */
//    public function lookupStoreIds($contactId)
//    {
//        $adapter = $this->_getReadAdapter();
//        $select  = $adapter->select()
//            ->from($this->getTable('julfiker_contact/contact_store'), 'store_id')
//            ->where('contact_id = ?', (int)$contactId);
//        return $adapter->fetchCol($select);
//    }
//
//    /**
//     * Perform operations after object load
//     *
//     * @access public
//     * @param Mage_Core_Model_Abstract $object
//     * @return Julfiker_Contact_Model_Resource_Contact
//     * @author Ultimate Module Creator
//     */
//    protected function _afterLoad(Mage_Core_Model_Abstract $object)
//    {
//        return parent::_afterLoad($object);
//    }
//
//    /**
//     * Retrieve select object for load object data
//     *
//     * @param string $field
//     * @param mixed $value
//     * @param Julfiker_Contact_Model_Contact $object
//     * @return Zend_Db_Select
//     */
//    protected function _getLoadSelect($field, $value, $object)
//    {
//        $select = parent::_getLoadSelect($field, $value, $object);
//        return $select;
//    }
//
//    /**
//     * Assign contact to store views
//     *
//     * @access protected
//     * @param Mage_Core_Model_Abstract $object
//     * @return Julfiker_Contact_Model_Resource_Contact
//     * @author Ultimate Module Creator
//     */
//    protected function _afterSave(Mage_Core_Model_Abstract $object)
//    {
////        $oldStores = $this->lookupStoreIds($object->getId());
////        $newStores = (array)$object->getStores();
////        if (empty($newStores)) {
////            $newStores = (array)$object->getStoreId();
////        }
////        $table  = $this->getTable('julfiker_contact/contact_store');
////        $insert = array_diff($newStores, $oldStores);
////        $delete = array_diff($oldStores, $newStores);
////        if ($delete) {
////            $where = array(
////                'contact_id = ?' => (int) $object->getId(),
////                'store_id IN (?)' => $delete
////            );
////            $this->_getWriteAdapter()->delete($table, $where);
////        }
////        if ($insert) {
////            $data = array();
////            foreach ($insert as $storeId) {
////                $data[] = array(
////                    'contact_id'  => (int) $object->getId(),
////                    'store_id' => (int) $storeId
////                );
////            }
////            $this->_getWriteAdapter()->insertMultiple($table, $data);
////        }
//        return parent::_afterSave($object);
//    }
//
//    /**
//     * check url key
//     *
//     * @access public
//     * @param string $urlKey
//     * @param int $storeId
//     * @param bool $active
//     * @return mixed
//     * @author Ultimate Module Creator
//     */
//    public function checkUrlKey($urlKey, $storeId, $active = true)
//    {
//        $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID, $storeId);
//        $select = $this->_initCheckUrlKeySelect($urlKey, $stores);
//        if ($active) {
//            $select->where('e.status = ?', $active);
//        }
//        $select->reset(Zend_Db_Select::COLUMNS)
//            ->columns('e.entity_id')
//            ->limit(1);
//
//        return $this->_getReadAdapter()->fetchOne($select);
//    }
//
//    /**
//     * Check for unique URL key
//     *
//     * @access public
//     * @param Mage_Core_Model_Abstract $object
//     * @return bool
//     * @author Ultimate Module Creator
//     */
//    public function getIsUniqueUrlKey(Mage_Core_Model_Abstract $object)
//    {
//        if (Mage::app()->isSingleStoreMode() || !$object->hasStores()) {
//            $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID);
//        } else {
//            $stores = (array)$object->getData('stores');
//        }
//        $select = $this->_initCheckUrlKeySelect($object->getData('url_key'), $stores);
//        if ($object->getId()) {
//            $select->where('e.entity_id <> ?', $object->getId());
//        }
//        if ($this->_getWriteAdapter()->fetchRow($select)) {
//            return false;
//        }
//        return true;
//    }
//
//    /**
//     * Check if the URL key is numeric
//     *
//     * @access public
//     * @param Mage_Core_Model_Abstract $object
//     * @return bool
//     * @author Ultimate Module Creator
//     */
//    protected function isNumericUrlKey(Mage_Core_Model_Abstract $object)
//    {
//        return preg_match('/^[0-9]+$/', $object->getData('url_key'));
//    }
//
//    /**
//     * Check if the URL key is valid
//     *
//     * @access public
//     * @param Mage_Core_Model_Abstract $object
//     * @return bool
//     * @author Ultimate Module Creator
//     */
//    protected function isValidUrlKey(Mage_Core_Model_Abstract $object)
//    {
//        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('url_key'));
//    }
//
//    /**
//     * format string as url key
//     *
//     * @access public
//     * @param string $str
//     * @return string
//     * @author Ultimate Module Creator
//     */
//    public function formatUrlKey($str)
//    {
//        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($str));
//        $urlKey = strtolower($urlKey);
//        $urlKey = trim($urlKey, '-');
//        return $urlKey;
//    }
//
//    /**
//     * init the check select
//     *
//     * @access protected
//     * @param string $urlKey
//     * @param array $store
//     * @return Zend_Db_Select
//     * @author Ultimate Module Creator
//     */
//    protected function _initCheckUrlKeySelect($urlKey, $store)
//    {
//        $select = $this->_getReadAdapter()->select()
//            ->from(array('e' => $this->getMainTable()))
//            ->join(
//                array('es' => $this->getTable('julfiker_contact/contact_store')),
//                'e.entity_id = es.contact_id',
//                array())
//            ->where('e.url_key = ?', $urlKey)
//            ->where('es.store_id IN (?)', $store);
//        return $select;
//    }
//
//    /**
//     * validate before saving
//     *
//     * @access protected
//     * @param $object
//     * @return Julfiker_Contact_Model_Resource_Contact
//     * @author Ultimate Module Creator
//     */
//    protected function _beforeSave(Mage_Core_Model_Abstract $object)
//    {
//        $conacttype = $object->getConactType();
//        if (is_array($conacttype)) {
//            $object->setConactType(implode(',', $conacttype));
//        }
//        $urlKey = $object->getData('url_key');
//        if ($urlKey == '') {
//            $urlKey = $object->getContactStatus();
//        }
//        $urlKey = $this->formatUrlKey($urlKey);
//        $validKey = false;
//        while (!$validKey) {
//            $entityId = $this->checkUrlKey($urlKey, $object->getStoreId(), false);
//            if ($entityId == $object->getId() || empty($entityId)) {
//                $validKey = true;
//            } else {
//                $parts = explode('-', $urlKey);
//                $last = $parts[count($parts) - 1];
//                if (!is_numeric($last)) {
//                    $urlKey = $urlKey.'-1';
//                } else {
//                    $suffix = '-'.($last + 1);
//                    unset($parts[count($parts) - 1]);
//                    $urlKey = implode('-', $parts).$suffix;
//                }
//            }
//        }
//        $object->setData('url_key', $urlKey);
//        return parent::_beforeSave($object);
//    }
}
