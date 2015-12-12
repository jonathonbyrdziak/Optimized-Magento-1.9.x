<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Widgetized_Recorder_Block_Adminhtml_Orders_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * 
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('orders_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
    /**
     * 
     * @return type
     */
    protected function _prepareCollection()
    {
        $collection = Mage::helper('recorder')->getAllSubscriptions();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * 
     * @return \Widgetized_Recorder_Block_Adminhtml_Orders_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('item_id');
        $this->getMassactionBlock()->setFormFieldName('recurring_ids');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => 'Delete',
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => 'Are you sure?'
        ));

        $this->getMassactionBlock()->addItem('reset', array(
            'label' => 'Reset',
            'url' => $this->getUrl('*/*/reset')
        ));

        $this->getMassactionBlock()->addItem('status', array(
            'label' => 'Change status',
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => 'Status',
                    'values' => array(
                        '1' => 'Enabled',
                        '0' => 'Disabled'
                    )
                )
            )
        ));
        return $this;
    }

    /**
     * 
     * @return type
     */
    protected function _prepareColumns()
    {
//        $this->addColumn('item_id', array(
//            'header_css_class' => 'a-center',
//            'header'     => Mage::helper('adminhtml')->__('Select'),
//            'type'       => 'checkbox',
//            'field_name' => 'options['.Widgetized_Recorder_Helper_Data::OPTIONSID.'][selected]',
//            'align'      => 'center',
//            'checked'    => $this->getEntireRange()==1 ? 'true' : 'false',
//        ));
        
        $this->addColumn('id', array(
            'header'    => Mage::helper('recorder')->__('ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'id',
        ));
 
        $this->addColumn('customer_id', array(
            'header'    => Mage::helper('recorder')->__('Shipping Address'),
            'align'     => 'left',
            'width'     => '150px',
            'index'     => 'customer_id',
            'renderer'  => 'recorder/adminhtml_renderer_customer'
        ));
 
        $this->addColumn('start_date', array(
            'header'    => Mage::helper('recorder')->__('Next Order Date'),
            'align'     => 'center',
            'index'     => 'start_date',
            'format'    => 'M jS Y',
            'width'     => '50px',
            'renderer'  => 'recorder/adminhtml_renderer_date'
        ));
 
        $this->addColumn('totals', array(
            'header'    => Mage::helper('recorder')->__('Totals'),
            'align'     => 'center',
            'index'     => 'totals',
            'width'     => '300px',
            'renderer'  => 'recorder/adminhtml_renderer_attribute'
        ));
 
        $this->addColumn('status', array(
            'header'    => Mage::helper('recorder')->__('Status'),
            'align'     => 'center',
            'index'     => 'status',
            'width'     => '50px',
            'renderer'  => 'recorder/adminhtml_renderer_enabled'
        ));
 
        $this->addColumn('order_processing', array(
            'header'    => Mage::helper('recorder')->__('Errors Preventing A Good Order'),
            'align'     => 'center',
            'index'     => 'order_processing',
            'renderer'  => 'recorder/adminhtml_renderer_ready'
        ));
 
//        $this->addColumn('reminder_sent', array(
//            'header'    => Mage::helper('recorder')->__('Reminder Sent'),
//            'align'     => 'center',
//            'index'     => 'reminder_sent',
//            'width'     => '50px',
//            'renderer'  => 'recorder/adminhtml_renderer_attribute'
//        ));
 
//        $this->addColumn('shipping_amount', array(
//            'header'    => Mage::helper('recorder')->__('Shipping'),
//            'align'     => 'center',
//            'index'     => 'shipping_amount',
//            'width'     => '50px',
//            'renderer'  => 'recorder/adminhtml_renderer_attribute'
//        ));
// 
//        $this->addColumn('taxes', array(
//            'header'    => Mage::helper('recorder')->__('Taxes'),
//            'align'     => 'center',
//            'index'     => 'tax_amount',
//            'width'     => '50px',
//            'renderer'  => 'recorder/adminhtml_renderer_attribute'
//        ));
// 
//        $this->addColumn('grand_total', array(
//            'header'    => Mage::helper('recorder')->__('Grand Total'),
//            'align'     => 'center',
//            'index'     => 'grand_total',
//            'width'     => '50px',
//            'renderer'  => 'recorder/adminhtml_renderer_attribute'
//        ));
 
        return parent::_prepareColumns();
    }
 
    /**
     * 
     * @param type $row
     * @return boolean
     */
    public function getRowUrl($row)
    {
        return false;
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}