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

class Zeon_Faq_Model_Mysql4_Faq_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('zeon_faq/faq');
        $this->_map['fields']['faq_id'] = 'main_table.faq_id';
        $this->_map['fields']['title'] = 'main_table.title';
        $this->_map['fields']['is_most_frequently'] = 'main_table.is_most_frequently';
        $this->_map['fields']['update_time'] = 'main_table.update_time';
        $this->_map['fields']['status'] = 'main_table.status';
    }

    /**
     * Add stores column
     *
     * @return Zeon_Faq_Model_Mysql4_Faq_Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getFlag('add_stores_column')) {
            $this->_addStoresVisibility();
        }
        $this->_addCategory();
        return $this;
    }
    /**
     * Set add stores column flag
     *
     * @return Zeon_Faq_Model_Mysql4_Faq_Collection
     */
    public function addStoresVisibility()
    {
        $this->setFlag('add_stores_column', true);
        return $this;
    }
    /**
     * Collect and set stores ids to each collection item
     * Used in faq grid as Visible in column info
     *
     * @return Zeon_Faq_Model_Mysql4_Faq_Collection
     */
    protected function _addStoresVisibility()
    {
        $faqIds = $this->getColumnValues('faq_id');
        $faqStores = array();
        if (sizeof($faqIds)>0) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('zeon_faq/store'), array('store_id', 'faq_id'))
                ->where('faq_id IN(?)', $faqIds);
            $faqRaw = $this->getConnection()->fetchAll($select);
            foreach ($faqRaw as $faq) {
                if (!isset($faqStores[$faq['faq_id']])) {
                    $faqStores[$faq['faq_id']] = array();
                }

                $faqStores[$faq['faq_id']][] = $faq['store_id'];
            }
        }

        foreach ($this as $item) {
            if (isset($faqStores[$item->getId()])) {
                $item->setStores($faqStores[$item->getId()]);
            } else {
                $item->setStores(array());
            }
        }

        return $this;
    }

    /**
     * Collect and set category title to each collection item
     * Used in news/event grid as Category in column info
     *
     * @return Zeon_News_Model_Resource_News_Collection
     */
    protected function _addCategory()
    {
        $categoryIds = $this->getColumnValues('category_id');
        $categories = array();
        if (sizeof($categoryIds)>0) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('zeon_faq/category'), array('category_id','title'))
                ->where('category_id IN(?)', $categoryIds);
            $categoryRaw = $this->getConnection()->fetchAll($select);

            foreach ($categoryRaw as $category) {
                if (!isset($categories[$category['category_id']])) {
                    $categories[$category['category_id']] = array();
                }

                $categories[$category['category_id']] = $category['title'];
            }
        }

        foreach ($this as $item) {
            if (isset($categories[$item->getCategoryId()])) {
                $item->setCategoryName($categories[$item->getCategoryId()]);
            } else {
                $item->setCategoryName('');
            }
        }

        return $this;
    }

    /**
     * Add Filter by store
     *
     * @param int|array $storeIds
     * @param bool $withAdmin
     * @return Zeon_Faq_Model_Mysql4_Faq_Collection
     */
    public function addStoreFilter($storeIds, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter')) {
            if ($withAdmin) {
                $storeIds = array(0, $storeIds);
            }

            $this->getSelect()->join(
                array('store_table' => $this->getTable('zeon_faq/store')),
                'main_table.faq_id = store_table.faq_id',
                array()
            )
            ->where('store_table.store_id in (?)', $storeIds);
            $this->setFlag('store_filter', true);
        }
        return $this;
    }

    /**
     * Add Filter by category
     *
     * @param string $categoryTitle
     * @return Zeon_Faq_Model_Mysql4_Faq_Collection
     */
    public function addCategoryFilter($categoryTitle)
    {
        if (!$this->getFlag('category_filter')) {
            $this->getSelect()->join(
                array('category_table' => $this->getTable('zeon_faq/category')),
                'main_table.category_id = category_table.category_id',
                array()
            )
            ->where('category_table.title like (?)', $categoryTitle);

            $this->setFlag('category_filter', true);
        }
        return $this;
    }

    /**
     * Add Filter by search
     *
     * @param array $fields
     * @param string $value
     * @return Zeon_Faq_Model_Resource_Faq_Collection
     */
    public function addSearchFilter($value)
    {
        if ($value) {
            $value = trim($value);
            $value = Mage::helper('core/string')->cleanString($value);
            $this->getSelect()->where('main_table.title like (?) or description like (?)', "%$value%");
        }
        return $this;
    }
}