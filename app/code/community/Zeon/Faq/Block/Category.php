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

class Zeon_Faq_Block_Category extends Mage_Core_Block_Template
{
    protected $_categoryCollection = null;
    protected $_category = null;

    /**
     * Set active category if any.
     *
     * return Zeon_Faq_Block_Category
     */
    protected function _beforeToHtml()
    {
        $categoryId = $this->getRequest()->getParam('category', null);
        $model = Mage::getModel('zeon_faq/category');
        if ($categoryId) {
            $category = $model->load($categoryId);
            $this->setCurrentCategory($category);
        }

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve Category collection
     *
     * @return Zeon_Faq_Model_Resource_Category_Collection
     */
    public function getCategoryCollection()
    {
         if (is_null($this->_categoryCollection)) {
             $this->_categoryCollection = Mage::getResourceModel('zeon_faq/category_collection')
                                         ->addFieldToFilter('main_table.status', Zeon_Faq_Model_Status::STATUS_ENABLED)
                                         ->addOrder('sort_order', 'asc');
         }
         $this->_categoryCollection->getSelect()->distinct()
            ->join(
                array('zfc'=> Mage::getResourceModel('zeon_faq/faq')->getTable('zeon_faq/faq')), 
                'main_table.category_id = zfc.category_id', array('category_id')
            );
         return $this->_categoryCollection;
    }

    /**
     * Set current category.
     *
     * @param $label
     * @return Zeon_Faq_Block_Category
     */
    public function setCurrentCategory($category)
    {
        $this->_category = $category;
        return $this;
    }

    /**
     * Get current category.
     *
     * @return object
     */
    public function getCurrentCategory()
    {
        return $this->_category;
    }
    /**
     * Check for current category.
     *
     * @return object
     */
    public function isActiveCategory($category)
    {
         if (!is_null($this->getCurrentCategory())) {
             return ($this->getCurrentCategory()->getCategoryId() === $category->getCategoryId());
         }
         return false;
    }
}