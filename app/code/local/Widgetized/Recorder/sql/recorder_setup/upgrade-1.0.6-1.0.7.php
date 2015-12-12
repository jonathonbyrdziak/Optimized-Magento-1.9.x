<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('recurring_orders')} 
    ADD `quote_id` int NOT NULL; 
");
$installer->endSetup();
