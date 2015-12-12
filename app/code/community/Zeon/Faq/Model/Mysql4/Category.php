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

class Zeon_Faq_Model_Mysql4_Category extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        // Note that the category_id refers to the key field in your database table.
        $this->_init('zeon_faq/category', 'category_id');
    }

    /**
     * Process faq category before deleting
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Zeon_Faq_Model_Mysql4_Category
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        $this->_getWriteAdapter()->update(
            $this->getTable('zeon_faq/faq'),
            array('category_id' => new Zend_Db_Expr('NULL')),
            array('category_id = ?' => (int)$object->getId())
        );
        return parent::_beforeDelete($object);
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
            'title' => Mage::helper('zeon_faq')->__('Faq category with the same title')
        ));
        return $this;
    }

    /**
     * Process category data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Zeon_Faq_Model_Mysql4_Category
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
     * Retrieve load select with filter by identifier, store and activity
     *
     * @param string $identifier
     * @param int|array $store
     * @param int $isActive
     * @return Varien_Db_Select
     */
    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('ca' => $this->getMainTable()))
            ->where('ca.identifier = ?', $identifier);
        if (!is_null($isActive)) {
            $select->where('ca.status = ?', $isActive);
        }

        return $select;
    }
    /**
     * Check if faq identifier exist for specific store
     * return page id if page exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID, $storeId);
        $select = $this->_getLoadByIdentifierSelect($identifier, $stores, 1);
        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns('ca.category_id')
            ->limit(1);
        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Retrieves faq category title from DB by passed id.
     *
     * @param string $id
     * @return string|false
     */
    public function getFaqCategoryTitleById($id)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getMainTable(), 'title')
            ->where('category_id = :category_id');

        $binds = array('category_id' => (int) $id);
        return $adapter->fetchOne($select, $binds);
    }
}
