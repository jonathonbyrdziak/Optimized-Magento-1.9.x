<?php
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE  `{$installer->getTable('sales_flat_quote_address')}` ADD  `is_ups_invalid` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT  '0'");
$installer->run("ALTER TABLE  `{$installer->getTable('sales_flat_order_address')}` ADD  `is_ups_invalid` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT  '0'");


$installer->endSetup();