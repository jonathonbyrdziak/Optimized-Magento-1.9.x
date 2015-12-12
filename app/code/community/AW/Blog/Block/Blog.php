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


class AW_Blog_Block_Blog extends AW_Blog_Block_Abstract
{
    public function getPosts()
    {
        $collection = parent::_prepareCollection();
        $post = Mage::app()->getRequest()->getParams();
        if (isset($post['tag'])) {
            $collection->addTagFilter(urldecode($post['tag']));
            
        } elseif (isset($post['q'])) {
            $collection->addSearchFilter(urldecode($post['q']));
            
        } elseif (isset($post['y']) && isset($post['m'])) {
            $collection->addArchiveFilter(urldecode($post['m']), urldecode($post['y']));
            
        }
        parent::_processCollection($collection);
        return $collection;
    }

    protected function _prepareLayout()
    {
        if ($this->isBlogPage() && ($breadcrumbs = $this->getCrumbs())) {
            parent::_prepareMetaData(self::$_helper);
            $tag = $this->getRequest()->getParam('tag', false);
            if ($tag) {
                $tag = urldecode($tag);
                $breadcrumbs->addCrumb(
                    'blog',
                    array(
                        'label' => self::$_helper->getTitle(),
                        'title' => $this->__('Return to ' . self::$_helper->getTitle()),
                        'link'  => $this->getBlogUrl(),
                    )
                );
                $breadcrumbs->addCrumb(
                    'blog_tag',
                    array(
                        'label' => $this->__('Tagged with "%s"', self::$_helper->convertSlashes($tag)),
                        'title' => $this->__('Tagged with "%s"', $tag),
                    )
                );
            } else {
                $breadcrumbs->addCrumb('blog', array('label' => self::$_helper->getTitle()));
            }
        }
    }
}