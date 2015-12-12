<?php
/*------------------------------------------------------------------------
# $JA#PRODUCT_NAME$ - Version $JA#VERSION$ - Licence Owner $JA#OWNER$
# ------------------------------------------------------------------------
# Copyright (C) 2004-2009 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: J.O.O.M Solutions Co., Ltd
# Websites: http://www.joomlart.com - http://www.joomlancers.com
# This file may not be redistributed in whole or significant part.
-------------------------------------------------------------------------*/
    $installer = $this;
	$installer->startSetup();
	$installer->run("
		DROP TABLE IF EXISTS {$this->getTable('jmmegamenu')};
		CREATE TABLE {$this->getTable('jmmegamenu')} (
		  `menu_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `title` varchar(255) NOT NULL DEFAULT '',
		  `link` text,
		  `url` varchar(255) DEFAULT NULL,
		  `catid` int(11) DEFAULT NULL,
		  `menualias` varchar(255) DEFAULT NULL,
          `menutype` varchar(50) NOT NULL DEFAULT '',
		  `category` varchar(255) DEFAULT NULL,
		  `cms` varchar(255) DEFAULT NULL,
		  `parent` int(11) unsigned NOT NULL DEFAULT '0',
		  `lft` int(11) unsigned NOT NULL DEFAULT '0',
		  `rgt` int(11) unsigned NOT NULL DEFAULT '0',
		  `mega_cols` int(11) unsigned NOT NULL DEFAULT '0',
		  `mega_group` smallint(6) NOT NULL DEFAULT '0',
		  `mega_class` varchar(255) NOT NULL DEFAULT '',
		  `status` smallint(6) NOT NULL DEFAULT '0',
		  `ordering` int(11) DEFAULT '0',
		  `showtitle` tinyint(6) NOT NULL DEFAULT '1',
		  `menugroup` int(11) unsigned NOT NULL,
		  `created_time` datetime DEFAULT NULL,
		  `update_time` datetime DEFAULT NULL,
		  `static_block` varchar(255) DEFAULT NULL,
		  `mega_subcontent` int(11) NOT NULL DEFAULT '1',
		  `mega_width` int(11) DEFAULT NULL,
		  `mega_colw` int(11) DEFAULT NULL,
		  `mega_colxw` varchar(255) DEFAULT NULL,
		  `desc` text,
		  `browserNav` tinyint(4) NOT NULL DEFAULT '0',
		  `contentxml` text,
		  PRIMARY KEY (`menu_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
           
         DROP TABLE IF EXISTS {$this->getTable('jmmegamenu_store_menugroup')};  
         CREATE TABLE IF NOT EXISTS `{$this->getTable('jmmegamenu_store_menugroup')}` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `store_id` int(11) unsigned NOT NULL,
		  `menugroupid` int(11) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `store_id` (`store_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;


        DROP TABLE IF EXISTS {$this->getTable('jmmegamenu_types')};  
        CREATE TABLE IF NOT EXISTS `{$this->getTable('jmmegamenu_types')}` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `menutype` varchar(75) NOT NULL DEFAULT '',
		  `title` varchar(255) NOT NULL DEFAULT '',
		  `description` varchar(255) NOT NULL DEFAULT '',
		  `storeid` int(10) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `menutype` (`menutype`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;


	");
	
	$installer->endSetup();
	