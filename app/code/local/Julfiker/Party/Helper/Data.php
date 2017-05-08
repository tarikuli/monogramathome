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
 * Party default helper
 *
 * @category    Julfiker
 * @package     Julfiker_Party
 * @author      Julfiker
 */
class Julfiker_Party_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * convert array to options
     *
     * @access public
     * @param $options
     * @return array
     * @author Julfiker
     */
    public function convertOptions($options)
    {
        $converted = array();
        foreach ($options as $option) {
            if (isset($option['value']) && !is_array($option['value']) &&
                isset($option['label']) && !is_array($option['label'])) {
                $converted[$option['value']] = $option['label'];
            }
        }
        return $converted;
    }

    /**
     * Get All customers for drop-down options
     *
     * @return array
     */
    public function getCustomerOptions(){
        $options[] = array();
        $customers = Mage::getModel('customer/customer');
        $ids = $customers->getCollection()->getAllIds();

        if ($ids)
        {
            foreach ($ids as $id)
            {
                $customer = Mage::getModel('customer/customer');
                $customer->load($id);

                $options[] = array(
                    'value' => $customer->getID(),
                    'label' => $customer->getName()
                );
            }
        }

        return $options;

    }
}
