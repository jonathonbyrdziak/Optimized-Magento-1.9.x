<?php
$installer = $this;

$installer->startSetup();
	$installer->run("
	ALTER TABLE {$this->getTable('jmmegamenu')} ADD COLUMN `image` varchar(255) DEFAULT '';
	ALTER TABLE {$this->getTable('jmmegamenu')} ADD COLUMN `description` text DEFAULT ''
	");
$installer->endSetup();
