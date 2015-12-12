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


class AW_Blog_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_TAGCLOUD_SIZE = 'blog/menu/tagcloud_size';
    const XML_RECENT_SIZE = 'blog/menu/recent';

    const XML_BLOG_PERPAGE = 'blog/blog/perpage';
    const XML_BLOG_READMORE = 'blog/blog/readmore';
    const XML_BLOG_PARSE_CMS = 'blog/blog/parse_cms';

    const XML_BLOG_USESHORTCONTENT = 'blog/blog/useshortcontent';

    const XML_COMMENTS_PER_PAGE = 'blog/comments/page_count';
}