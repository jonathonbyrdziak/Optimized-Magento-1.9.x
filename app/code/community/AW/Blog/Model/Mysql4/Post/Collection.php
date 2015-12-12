<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento professional edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Blog
 * @version    1.3.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Blog_Model_Mysql4_Post_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_previewFlag;

    protected function _construct()
    {
        $this->_init('blog/blog');
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray('identifier', 'title');
    }

    public function addStoreFilter($store)
    {
        if (!Mage::app()->isSingleStoreMode()) {
            if ($store instanceof Mage_Core_Model_Store) {
                $store = $store->getId();
            }

            $store = (array)$store;
            array_push($store, 0);

            $this
                ->getSelect()
                ->distinct()
                ->join(
                    array('store_table' => $this->getTable('store')),
                    'main_table.post_id = store_table.post_id',
                    array()
                )
                ->where('store_table.store_id in (?)', array($store))
            ;
        }

        return $this;
    }

    public function addStatusFilter(
        $status = array(AW_Blog_Model_Status::STATUS_ENABLED, AW_Blog_Model_Status::STATUS_HIDDEN)
    )
    {
        if ($status == '*') {
            $status = array(
                AW_Blog_Model_Status::STATUS_ENABLED,
                AW_Blog_Model_Status::STATUS_HIDDEN,
                AW_Blog_Model_Status::STATUS_DISABLED,
            );
        }

        if (is_string($status)) {
            $status = (array)$status;
        }

        $this->getSelect()->where('main_table.status IN (?)', $status);

        return $this;
    }
}