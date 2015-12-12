<?php
$installer = $this;

$installer->startSetup();

$this->addAttribute('customer_address', 'is_ups_invalid', array(
    'type' => 'int',
    'input' => 'text',
    'label' => 'Is UPS invalid?',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));

Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'is_ups_invalid')
    ->setData('used_in_forms', array('adminhtml_customer_address'))
    ->save();

$installer->endSetup();
