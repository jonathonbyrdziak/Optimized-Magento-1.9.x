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


class AW_Blog_Block_Last extends AW_Blog_Block_Menu_Sidebar implements Mage_Widget_Block_Interface
{
    protected function _toHtml()
    {
        $this->setTemplate('aw_blog/widget_post.phtml');
        if ($this->_helper()->getEnabled()) {
            return $this->setData('blog_widget_recent_count', $this->getBlocksCount())->renderView();
        }
    }

    public function getRecent()
    {
        $collection = Mage::getModel('blog/blog')->getCollection()
            ->addPresentFilter()
            ->addEnableFilter(AW_Blog_Model_Status::STATUS_ENABLED)
            ->addStoreFilter()
            ->setOrder('created_time', 'desc')
        ;

        if ($this->getBlogCount()) {
            $collection->setPageSize($this->getBlogCount());
        } else {
            $collection->setPageSize(Mage::helper('blog')->getRecentPage());
        }

        if ($collection && $this->getData('categories')) {
            $collection->addCatsFilter($this->getData('categories'));
        }
        foreach ($collection as $item) {
            $item->setAddress($this->getBlogUrl($item->getIdentifier()));
        }
        return $collection;
    }
}