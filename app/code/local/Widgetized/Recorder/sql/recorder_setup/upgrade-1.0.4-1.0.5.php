<?php

$installer = $this;
$installer->startSetup();
/**
 * Description of Order
 *
 * 
 * 
 * @var id
 * @var parent_id
 * @var increment_id
 * @var subtotal
 * @var shipping_amount
 * @var tax_amount
 * @var grand_total
 * @var skus
 * @var billing_address
 * @var shipping_address
 * @var customer_id
 * @var recurring_interval
 * @var recurring_start_date
 * @var failed_attempt
 * @var reminder_sent
 * 
 */
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('recurring_orders')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `subtotal` varchar(255) NOT NULL,
  `shipping_amount` varchar(255) NOT NULL,
  `tax_amount` varchar(255) NOT NULL,
  `grand_total` varchar(255) NOT NULL,
  `skus` varchar(255) NOT NULL,
  `billing_address` varchar(255) NOT NULL,
  `shipping_address` varchar(255) NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `interval` varchar(255) NOT NULL,
  `start_date` varchar(255) NOT NULL,
  `failed_attempt` varchar(255) NOT NULL,
  `reminder_sent` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
