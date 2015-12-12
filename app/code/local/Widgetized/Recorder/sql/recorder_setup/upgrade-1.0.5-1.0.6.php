<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('recurring_orders')} 
    ADD `created_at` VARCHAR(50) NOT NULL, 
    ADD `updated_on` VARCHAR(50) NOT NULL,
    ADD `errors` VARCHAR(255) NOT NULL,
    ADD `store_id` VARCHAR(25) NOT NULL,
    ADD `shipping_description` VARCHAR(255) NOT NULL,
    ADD `totals` TEXT NOT NULL,
    ADD `enabled` VARCHAR(50) NOT NULL; 
");
$installer->endSetup();
