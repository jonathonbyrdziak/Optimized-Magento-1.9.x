<?php

/**
 * zeonsolutions inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.zeonsolutions.com/shop/license-community.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * This package designed for Magento COMMUNITY edition
 * =================================================================
 * zeonsolutions does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * zeonsolutions does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   Zeon
 * @package    Zeon_Faq
 * @version    0.0.1
 * @copyright  @copyright Copyright (c) 2013 zeonsolutions.Inc. (http://www.zeonsolutions.com)
 * @license    http://www.zeonsolutions.com/shop/license-community.txt
 */

class Zeon_Faq_Model_Mysql4_Faq extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        // Note that the faq_id refers to the key field in your database table.
        $this->_init('zeon_faq/faq', 'faq_id');
    }

    /**
     * Process news data before deleting
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Zeon_News_Model_Mysql4_News
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        $condition = array(
            'faq_id = ?'     => (int) $object->getId(),
        );

        $this->_getWriteAdapter()->delete($this->getTable('zeon_faq/store'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Process category data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Zeon_Faq_Model_Mysql4_Faq
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        // modify create / update dates
        if ($object->isObjectNew() && !$object->hasCreationTime()) {
            $object->setCreationTime(Mage::getSingleton('core/date')->gmtDate());
        }

        $object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * Initialize unique fields
     *
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(array(
            'field' => 'title',
            'title' => Mage::helper('zeon_faq')->__('FAQ with the same title')
        ));
        return $this;
    }

    /**
     * Load store Ids array
     *
     * @param Zeon_Faq_Model_Faq $object
     */
    public function loadStoreIds(Zeon_Faq_Model_Faq $object)
    {
        $faqId   = $object->getId();
        $storeIds = array();
        if ($faqId) {
            $storeIds = $this->lookupStoreIds($faqId);
        }
        $object->setStoreIds($storeIds);
    }
    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        return $this->_getReadAdapter()->fetchCol(
            $this->_getReadAdapter()->select()->from(
                $this->getTable('zeon_faq/store'),
                'store_id'
            )
            ->where("{$this->getIdFieldName()} = :id_field"),
            array(':id_field' => $id)
        );
    }
    /**
     * Delete current faq from the table zeon_faq_store and then
     * insert to update "faq to store" relations
     *
     * @param Mage_Core_Model_Abstract $object
     */
    public function saveFaqStore(Mage_Core_Model_Abstract $object)
    {
        /** stores */
        $deleteWhere = $this->_getReadAdapter()->quoteInto('faq_id = ?', $object->getId());
        $this->_getReadAdapter()->delete($this->getTable('zeon_faq/store'), $deleteWhere);
        foreach ($object->getStoreIds() as $storeId) {
            $faqStoreData = array(
            'faq_id' => $object->getId(),
            'store_id' => $storeId
        );
        $this->_getWriteAdapter()->insert($this->getTable('zeon_faq/store'), $faqStoreData);
            if ($storeId === '0') {
                break;
            }
        }
    }


}
