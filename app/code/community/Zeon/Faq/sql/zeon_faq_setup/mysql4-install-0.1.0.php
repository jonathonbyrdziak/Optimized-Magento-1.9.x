<?php

/**
 * zeonsolutions inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.zeonsolutions.com/shop/license-community.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * This package designed for Magento COMMUNITY edition
 * =================================================================
 * zeonsolutions does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * zeonsolutions does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   Zeon
 * @package    Zeon_Faq
 * @version    0.0.1
 * @copyright  @copyright Copyright (c) 2013 zeonsolutions.Inc. (http://www.zeonsolutions.com)
 * @license    http://www.zeonsolutions.com/shop/license-community.txt
 */

/* @var $installer Zeon_Faq_Model_Mysql4_Setup */
$installer = $this;

$installer->startSetup();
$installer->run(
    "/* Table structure for table `zeon_faq` */

DROP TABLE IF EXISTS {$this->getTable('zeon_faq/faq')};
 CREATE TABLE {$this->getTable('zeon_faq/faq')} (
    `faq_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Faq Id',
    `title` varchar(255) DEFAULT NULL COMMENT 'Title',
    `category_id` int(10) unsigned DEFAULT NULL COMMENT 'Category Id',
    `status` smallint(6) NOT NULL COMMENT 'Status',
    `is_most_frequently` varchar(255) DEFAULT NULL COMMENT 'Is Most Frequently',
    `description` text NOT NULL COMMENT 'Description',
    `sort_order` smallint(6) DEFAULT NULL COMMENT 'Sort Order',
    `creation_time` datetime DEFAULT NULL COMMENT 'Creation Time',
    `update_time` datetime DEFAULT NULL COMMENT 'Update Time',
    PRIMARY KEY (`faq_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Zeon Faq';

/*Table structure for table `zeon_faq_store` */

DROP TABLE IF EXISTS {$this->getTable('zeon_faq/store')};
CREATE TABLE {$this->getTable('zeon_faq/store')} (
    `faq_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Faq Id',
    `store_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store Id',
    PRIMARY KEY (`faq_id`,`store_id`),
    KEY `IDX_ZEON_FAQ_STORE_FAQ_ID` (`faq_id`),
    KEY `IDX_ZEON_FAQ_STORE_STORE_ID` (`store_id`),
    CONSTRAINT `FK_ZEON_FAQ_STORE_FAQ_ID_ZEON_FAQ_FAQ_ID` FOREIGN KEY (`faq_id`) 
    REFERENCES {$this->getTable('zeon_faq/faq')} (`faq_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_ZEON_FAQ_STORE_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) 
    REFERENCES {$this->getTable('core_store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Zeon Faq Store';

/*Table structure for table `zeon_faq_category` */

DROP TABLE IF EXISTS {$this->getTable('zeon_faq/category')};
CREATE TABLE {$this->getTable('zeon_faq/category')} (
    `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Category Id',
    `identifier` varchar(255) DEFAULT NULL COMMENT 'Identifier',
    `title` varchar(255) DEFAULT NULL COMMENT 'Title',
    `sort_order` smallint(6) DEFAULT NULL COMMENT 'Sort Order',
    `status` smallint(6) NOT NULL COMMENT 'Status',
    `creation_time` datetime DEFAULT NULL COMMENT 'Creation Time',
    `update_time` datetime DEFAULT NULL COMMENT 'Update Time',
    PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Zeon Faq Category';"
);

$installer->endSetup();