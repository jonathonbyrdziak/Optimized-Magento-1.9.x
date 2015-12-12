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
    ALTER TABLE {$this->getTable('blog/lblog')} ADD `comments` TINYINT(11) NOT NULL AFTER `meta_description`;
");
$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('blog/lstore')};
    CREATE TABLE {$this->getTable('blog/lstore')} (
        `post_id` smallint(6) unsigned,
        `store_id` smallint(6) unsigned
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8;

    DROP TABLE IF EXISTS {$this->getTable('blog/lcat_store')};
    CREATE TABLE {$this->getTable('blog/lcat_store')} (
        `cat_id` smallint(6) unsigned ,
        `store_id` smallint(6) unsigned
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8;

    DROP TABLE IF EXISTS {$this->getTable('blog/lpost_cat')};
    CREATE TABLE {$this->getTable('blog/lpost_cat')} (
        `cat_id` smallint(6) unsigned ,
        `post_id` smallint(6) unsigned
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8;
");
$installer->endSetup();