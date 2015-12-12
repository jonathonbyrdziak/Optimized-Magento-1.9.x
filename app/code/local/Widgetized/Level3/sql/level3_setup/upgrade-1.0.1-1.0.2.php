<?php

$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('level3_payment_methods')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `customer_id` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `number` int(25) NOT NULL,
  `month` int(2) NOT NULL,
  `year` int(4) NOT NULL,
  `primary` int(2) NOT NULL,
  `token` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
