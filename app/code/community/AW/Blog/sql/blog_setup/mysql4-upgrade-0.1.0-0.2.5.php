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

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('blog/lblog')} ADD `cat_id` TINYINT( 11 ) NOT NULL AFTER `post_id` ;
");

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('blog/lcat')};
    CREATE TABLE {$this->getTable('blog/lcat')} (
        `cat_id` int( 11 ) unsigned NOT NULL AUTO_INCREMENT ,
        `title` varchar( 255 ) NOT NULL default '',
        `identifier` varchar( 255 ) NOT NULL default '',
        `sort_order` tinyint ( 6 ) NOT NULL ,
        `meta_keywords` text NOT NULL ,
        `meta_description` text NOT NULL ,
        PRIMARY KEY ( `cat_id` )
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8;

    INSERT INTO {$this->getTable('blog/lcat')} (`cat_id`, `title`, `identifier`) VALUES (NULL, 'News', 'news');
");
$installer->endSetup();