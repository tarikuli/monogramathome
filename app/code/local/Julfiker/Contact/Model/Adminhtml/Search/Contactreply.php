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
 * Admin search model
 *
 * @category    Julfiker
 * @package     Julfiker_Contact
 * @author      Ultimate Module Creator
 */
class Julfiker_Contact_Model_Adminhtml_Search_Contactreply extends Varien_Object
{
    /**
     * Load search results
     *
     * @access public
     * @return Julfiker_Contact_Model_Adminhtml_Search_Contactreply
     * @author Ultimate Module Creator
     */
    public function load()
    {
        $arr = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        $collection = Mage::getResourceModel('julfiker_contact/contactreply_collection')
            ->addFieldToFilter('contact_id', array('like' => $this->getQuery().'%'))
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->load();
        foreach ($collection->getItems() as $contactreply) {
            $arr[] = array(
                'id'          => 'contactreply/1/'.$contactreply->getId(),
                'type'        => Mage::helper('julfiker_contact')->__('Contact reply'),
                'name'        => $contactreply->getContactId(),
                'description' => $contactreply->getContactId(),
                'url' => Mage::helper('adminhtml')->getUrl(
                    '*/contact_contactreply/edit',
                    array('id'=>$contactreply->getId())
                ),
            );
        }
        $this->setResults($arr);
        return $this;
    }
}
