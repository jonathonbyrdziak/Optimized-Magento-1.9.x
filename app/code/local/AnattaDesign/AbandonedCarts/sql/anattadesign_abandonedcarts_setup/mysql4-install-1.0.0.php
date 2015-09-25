<?php

$installer = $this;

$installer->startSetup();

$installer->run(
		"CREATE TABLE `" . $installer->getTable( 'anattadesign_abandonedcarts/opstatistics' ) . "` (
		`statistics_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`sales_flat_quote_id` int(10) unsigned NOT NULL,
		`step` varchar(32) NOT NULL,
		`reached` tinyint(4) NOT NULL DEFAULT '0',
		`moved` tinyint(4) NOT NULL DEFAULT '0',
		`year` smallint(6) NOT NULL,
		`month` tinyint(4) NOT NULL,
		`date` datetime NOT NULL,
		PRIMARY KEY (`statistics_id`),
		KEY `dashboard_tab` (`year`,`month`,`step`,`reached`,`moved`)
		) ENGINE=InnoDB"
);

$installer->run(
		"CREATE TABLE `" . $installer->getTable( 'anattadesign_abandonedcarts/statistics' ) . "` (
		`statistics_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`sales_flat_quote_id` int(10) unsigned NOT NULL,
		`step` varchar(32) NOT NULL,
		`reached` tinyint(4) NOT NULL DEFAULT '0',
		`moved` tinyint(4) NOT NULL DEFAULT '0',
		`year` smallint(6) NOT NULL,
		`month` tinyint(4) NOT NULL,
		`date` datetime NOT NULL,
		PRIMARY KEY (`statistics_id`),
		KEY `dashboard_tab` (`year`,`month`,`step`,`reached`,`moved`)
	) ENGINE=InnoDB"
);

$installer->endSetup();

Mage::helper( 'anattadesign_abandonedcarts' )->ping();