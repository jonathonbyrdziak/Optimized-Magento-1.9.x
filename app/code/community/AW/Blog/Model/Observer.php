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


class AW_Blog_Model_Observer
{
    public function addBlogSection($observer)
    {
        $sitemapObject = $observer->getSitemapObject();
        if (!($sitemapObject instanceof Mage_Sitemap_Model_Sitemap)) {
            throw new Exception(Mage::helper('blog')->__('Error during generation sitemap'));
        }

        $storeId = $sitemapObject->getStoreId();
        $date = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        /**
         * Generate blog pages sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/blog/changefreq');
        $priority = (string)Mage::getStoreConfig('sitemap/blog/priority');
        $collection = Mage::getModel('blog/blog')->getCollection()->addStoreFilter($storeId);
        Mage::getSingleton('blog/status')->addEnabledFilterToCollection($collection);
        $route = Mage::getStoreConfig('blog/blog/route');
        if ($route == "") {
            $route = "blog";
        }
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $route . '/' . $item->getIdentifier()), $date, $changefreq, $priority
            );

            $sitemapObject->sitemapFileAddLine($xml);
        }
        unset($collection);
    }

    public function rewriteRssList($observer)
    {
        if (Mage::helper('blog')->getEnabled()) {
            $node = Mage::getConfig()->getNode('global/blocks/rss/rewrite');
            foreach (Mage::getConfig()->getNode('global/blocks/rss/drewrite')->children() as $dnode) {
                $node->appendChild($dnode);
            }
        }
    }
}