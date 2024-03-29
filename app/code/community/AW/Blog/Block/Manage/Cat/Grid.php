<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento professional edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Blog
 * @version    1.3.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Blog_Block_Manage_Cat_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('categoryGrid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _getStoreId()
    {
        return $this->getRequest()->getParam('store', 0);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('blog/cat')->getCollection();
        $store = $this->_getStoreId();
        if ($store) {
            $collection
                ->addStoreFilter($store)
                ->getSelect()
                ->group('main_table.cat_id')
            ;
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'cat_id',
            array(
                 'header' => Mage::helper('blog')->__('ID'),
                 'align'  => 'right',
                 'width'  => '50px',
                 'index'  => 'cat_id',
            )
        );

        $this->addColumn(
            'title',
            array(
                 'header' => Mage::helper('blog')->__('Title'),
                 'align'  => 'left',
                 'index'  => 'title',
            )
        );

        $this->addColumn(
            'identifier',
            array(
                 'header' => Mage::helper('blog')->__('Identifier'),
                 'align'  => 'left',
                 'index'  => 'identifier',
            )
        );

        $this->addColumn(
            'sort_order',
            array(
                 'header' => Mage::helper('blog')->__('Sort Order'),
                 'align'  => 'left',
                 'width'  => '50px',
                 'index'  => 'sort_order',
            )
        );

        $this->addColumn(
            'action',
            array(
                 'header'    => Mage::helper('blog')->__('Action'),
                 'width'     => '100px',
                 'type'      => 'action',
                 'getter'    => 'getId',
                 'actions'   => array(
                     array(
                         'caption' => Mage::helper('blog')->__('Delete'),
                         'url'     => array('base' => '*/*/delete'),
                         'field'   => 'id',
                         'confirm' => $this->__('Are you sure?')
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

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('cat_id');
        $this->getMassactionBlock()->setFormFieldName('blog');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                 'label'   => Mage::helper('blog')->__('Delete'),
                 'url'     => $this->getUrl('*/*/massDelete'),
                 'confirm' => Mage::helper('blog')->__('Are you sure?'),
            )
        );

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}