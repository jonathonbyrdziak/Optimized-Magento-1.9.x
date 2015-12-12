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


class AW_Blog_Block_Cat extends AW_Blog_Block_Abstract
{
    public function getPosts()
    {
        $category = $this->getCategory();

        if (!$category->getCatId()) {
            return false;
        }
        $collection = parent::_prepareCollection()->addCatFilter($category->getCatId());
        parent::_processCollection($collection, $category);
        return $collection;
    }

    public function getCategory()
    {
        return Mage::getSingleton('blog/cat');
    }

    protected function _prepareLayout()
    {
        $post = $this->getCategory();
        $breadcrumbs = $this->getCrumbs();
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'blog',
                array(
                     'label' => self::$_helper->getTitle(),
                     'title' => $this->__('Return to %s', self::$_helper->getTitle()),
                     'link'  => $this->getBlogUrl(),
                )
            );
            $breadcrumbs->addCrumb('blog_page', array('label' => $post->getTitle(), 'title' => $post->getTitle()));
        }

        parent::_prepareMetaData($post);
    }

}
