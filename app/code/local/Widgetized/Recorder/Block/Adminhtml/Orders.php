<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Widgetized_Recorder_Block_Adminhtml_Orders extends Mage_Adminhtml_Block_Widget_Grid_Container {

    protected $_blockGroup = 'recorder';
    protected $_controller = 'adminhtml_orders';
    
    /**
     * 
     */
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('recorder')->__('Recurring Orders');

        $this->removeButton('add');
        $this->_addButton('process', array(
            'label'     => 'Process Orders Manually',
            'onclick'   => 'javascript:openOrderProcessing()',
            'class'     => 'add',
        ));
    }

    /**
     * 
     * @return type
     */
    protected function _prepareLayout()
    {
        $this->setChild( 'grid',
            $this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid',
            $this->_controller . '.grid')->setSaveParametersInSession(true)
        );
        return parent::_prepareLayout();
    }
}
