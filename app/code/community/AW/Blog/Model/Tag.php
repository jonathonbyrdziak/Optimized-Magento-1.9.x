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


class AW_Blog_Model_Tag extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('blog/tag');
    }

    public function refreshCount($store = null)
    {
        //Refreshes tag count
        $postsCount = Mage::getModel('blog/blog')->getCollection();
        if ($store) {
            $postsCount->addStoreFilter($store);
        }
        $postsCount = $postsCount->addTagFilter($this->getTag())->count();
        $this->setTagCount($postsCount)->save();
        return $this;
    }

    public function loadByName($name, $store = null)
    {
        $coll = Mage::getModel('blog/tag')->getCollection();

        $coll->getSelect()->where('tag=?', $name);
        if (!Mage::app()->isSingleStoreMode() && !is_null($store)) {
            $coll->getSelect()->where('store_id=?', $store);
        }

        foreach ($coll->load() as $item) {
            return $item;
        }

        if (!is_null($store)) {
            $this->setStoreId($store);
        }
        return $this->setTag($name);
    }
}