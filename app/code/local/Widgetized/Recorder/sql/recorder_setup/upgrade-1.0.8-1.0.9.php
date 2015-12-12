<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('recurring_orders')} 
    MODIFY `errors` VARCHAR(1255) NOT NULL;
");
$installer->endSetup();
