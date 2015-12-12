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

class Zeon_Faq_Block_Adminhtml_Faq_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set defaults
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('faqGrid');
        $this->setDefaultSort('faq_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Instantiate and prepare collection
     *
     * @return Zeon_Faq_Block_Adminhtml_List_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('zeon_faq/faq_collection');
        $collection->getSelect()->distinct()
            ->join(
                array('zfc'=> Mage::getResourceModel('zeon_faq/category')->getTable('zeon_faq/category')), 
                'main_table.category_id = zfc.category_id', 
                array('category_name'=>'title')
            );
        if (!Mage::app()->isSingleStoreMode()) {
            $collection->addStoresVisibility();
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    /**
     * Define grid columns
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'faq_id', 
            array(
                'header'=> Mage::helper('zeon_faq')->__('ID'),
                'type'  => 'number',
                'width' => '1',
                'index' => 'faq_id',
            )
        );

        $this->addColumn(
            'title', 
            array(
                'header' => Mage::helper('zeon_faq')->__('FAQ Title'),
                'type'   => 'text',
                'index'  => 'title',
            )
        );

        $this->addColumn(
            'faq_category', 
            array(
                'header' => Mage::helper('zeon_faq')->__('FAQ Category'),
                'type'   => 'text',
                'index'  => 'category_name',
            )
        );

        $this->addColumn(
            'is_most_frequently', 
            array(
                'header' => Mage::helper('zeon_faq')->__('Most Frequently'),
                'index'  => 'is_most_frequently',
                'type'    => 'options',
                'options' =>
                    array(
                        Zeon_Faq_Model_Status::STATUS_ENABLED  => Mage::helper('zeon_faq')->__('Yes'),
                        Zeon_Faq_Model_Status::STATUS_DISABLED => Mage::helper('zeon_faq')->__('No'),
                    ),
            )
        );

        $this->addColumn(
            'update_time', 
            array(
                'header'=> Mage::helper('zeon_faq')->__('Last Updated'),
                'type' => 'datetime',
                'index'=> 'update_time',
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

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'visible_in', 
                array(
                    'header'     => Mage::helper('zeon_faq')->__('Visible In'),
                    'type'       => 'store',
                    'index'      => 'stores',
                    'sortable'   => false,
                    'store_view' => true,
                    'width'      => 200
                )
            );
        }

        $this->addColumn(
            'action', 
            array(
                'header'  => Mage::helper('zeon_faq')->__('Action'),
                'width'   => '50',
                'type'    => 'action',
                'align'   => 'center',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('zeon_faq')->__('Edit'),
                        'url'     => array('base'=> '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'action',
                'is_system' => true,
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('faq_id');
        $this->getMassactionBlock()->setFormFieldName('faq');
        $this->getMassactionBlock()->addItem(
            'delete', 
            array(
                'label'        => Mage::helper('zeon_faq')->__('Delete'),
                'url'        => $this->getUrl('*/*/massDelete'),
                'confirm'    => Mage::helper('zeon_faq')->__('Are you sure you want to delete these faq?'),
            )
        );
        return $this;
    }

    /**
     * Grid row URL getter
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * Define row click callback
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * Add store filter
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column  $column
     * @return Zeon_News_Block_Adminhtml_News_Grid
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getIndex() == 'stores') {
            $this->getCollection()->addStoreFilter($column->getFilter()->getCondition(), false);
        } elseif ($column->getIndex() == 'category_name') {
            $this->getCollection()->addCategoryFilter($column->getFilter()->getCondition());
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
}