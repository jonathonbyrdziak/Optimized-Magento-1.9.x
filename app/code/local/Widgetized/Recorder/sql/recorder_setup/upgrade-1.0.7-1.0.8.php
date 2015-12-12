<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('recurring_orders')} 
    ADD `shipping_indicator` int NOT NULL; 
");
$installer->endSetup();
