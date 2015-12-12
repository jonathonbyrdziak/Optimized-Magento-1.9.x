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


class AW_Blog_Model_Mysql4_Tag_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_previewFlag;

    protected function _construct()
    {
        $this->_init('blog/tag');
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray('identifier', 'title');
    }

    public function addStoreFilter($store)
    {
        if (!Mage::app()->isSingleStoreMode()) {
            $id = $store->getId();
            $this->getSelect()->where('store_id=' . $id . ' OR store_id=0');
            return $this;
        }
        return $this;
    }

    public function addTagFilter($tag)
    {
        $this->getSelect()->where('tag=?', $tag);
        return $this;
    }

    public function getActiveTags()
    {
        $this->getSelect()
            ->columns(array('tag_final_count' => 'SUM(tag_count)'))
            ->joinLeft(
                array("stores" => $this->getTable('blog/store')), 'main_table.store_id = stores.store_id', array()
            )
            ->joinLeft(array("blogs" => $this->getTable('blog/blog')), "stores.post_id = blogs.post_id", array())
            ->where('blogs.status = 1')
            ->where('tag_count > 0')
            ->where('FIND_IN_SET(main_table.tag, blogs.tags)')
            ->where('main_table.store_id = ? OR main_table.store_id = 0', Mage::app()->getStore()->getId())
            ->order(array('tag_final_count DESC', 'tag'))
            ->limit(Mage::getStoreConfig(AW_Blog_Helper_Config::XML_TAGCLOUD_SIZE))
            ->group('tag')
        ;

        return $this;
    }
}