<?php
$this->startSetup();

$this->addAttribute('catalog_category', 'label_vote',  array(
    'type'       => 'int',
    'label'      => 'Vote',
    'input'      => 'select',
    'source'     => 'eav/entity_attribute_source_boolean',
    'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required'   => false,
    'sort_order' => '1',
    'default'    => 0,
    'group'      => 'Additional fields',
));

$this->addAttribute('catalog_category', 'label_new',  array(
    'type'       => 'int',
    'label'      => 'New',
    'input'      => 'select',
    'source'     => 'eav/entity_attribute_source_boolean',
    'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required'   => false,
    'sort_order' => '2',
    'default'    => 0,
    'group'      => 'Additional fields',
));

$this->addAttribute('catalog_category', 'link_title', array(
    'type'       => 'varchar',
    'label'      => 'Link title',
    'input'      => 'text',
    'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required'   => false,
    'sort_order' => '3',
    'group'      => 'Additional fields',
));

$this->addAttribute('catalog_category', 'box_class', array(
    'type'       => 'varchar',
    'label'      => 'Box class',
    'input'      => 'text',
    'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required'   => false,
    'sort_order' => '4',
    'group'      => 'Additional fields',
));

$this->endSetup();
