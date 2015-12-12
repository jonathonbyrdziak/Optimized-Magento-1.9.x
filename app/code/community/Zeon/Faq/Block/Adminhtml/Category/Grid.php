<?php

/**
 * zeonsolutions inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.zeonsolutions.com/shop/license-community.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * This package designed for Magento COMMUNITY edition
 * =================================================================
 * zeonsolutions does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * zeonsolutions does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   Zeon
 * @package    Zeon_Faq
 * @version    0.0.1
 * @copyright  @copyright Copyright (c) 2013 zeonsolutions.Inc. (http://www.zeonsolutions.com)
 * @license    http://www.zeonsolutions.com/shop/license-community.txt
 */

class Zeon_Faq_Block_Adminhtml_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Set defaults
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('categoryGrid');
        $this->setDefaultSort('category_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Instantiate and prepare collection
     *
     * @return Zeon_Faq_Block_Adminhtml_Category_Grid
     */

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('zeon_faq/category_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Define grid columns
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'category_id', 
            array(
                'header' => Mage::helper('zeon_faq')->__('ID'),
                'type'   => 'number',
                'width'  => '1',
                'index'  => 'category_id',
            )
        );

        $this->addColumn(
            'title', 
            array(
                'header' => Mage::helper('zeon_faq')->__('Name'),
                'type'   => 'text',
                'index'  => 'title',
            )
        );

        $this->addColumn(
            'status', 
            array(
                'header'  => Mage::helper('zeon_faq')->__('Status'),
                'align'   => 'center',
                'width'   => 1,
                'index'   => 'status',
                'type'    => 'options',
                'options' => Mage::getModel('zeon_faq/status')->getAllOptions(),
            )
        );

        $this->addColumn(
            'update_time', 
            array(
                'header' => Mage::helper('zeon_faq')->__('Last Updated'),
                'type'   => 'datetime',
                'index'  => 'update_time',
            )
        );

        $this->addColumn(
            'action', 
            array(
                'header'    =>  Mage::helper('zeon_faq')->__('Action'),
                'width'     => '50',
                'type'      => 'action',
                'align'     => 'center',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('zeon_faq')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            )
        );
        return parent::_prepareColumns();
    }

    /**
     * Prepare mass action options for this grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('category_id');
        $this->getMassactionBlock()->setFormFieldName('category');

        $this->getMassactionBlock()->addItem(
            'delete', 
            array(
                'label'        => Mage::helper('zeon_faq')->__('Delete'),
                'url'        => $this->getUrl('*/*/massDelete'),
                'confirm'    => Mage::helper('zeon_faq')->__('Are you sure you want to delete these category?')
            )
        );
        return $this;
    }

    /**
     * Grid row URL getter
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getCategoryId()));
    }

    /**
     * Define row click callback
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}