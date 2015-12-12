<?php
$installer = $this;

$installer->startSetup();
	$installer->run("
	ALTER TABLE {$this->getTable('jmmegamenu')} ADD COLUMN `shownumproduct` smallint(6) DEFAULT '0';
	");
$installer->endSetup();
