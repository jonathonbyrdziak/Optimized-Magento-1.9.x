<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Widgetized_Recorder_Block_Adminhtml_Renderer_Date extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
    public function render(Varien_Object $order)
    {
        $order = Mage::getModel('recorder/order')->load($order->getId());
        return date($this->getColumn()->getFormat(), strtotime($order->getData($this->getColumn()->getIndex())));
    }
}