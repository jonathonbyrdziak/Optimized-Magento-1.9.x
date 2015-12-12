<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Widgetized_Recorder_Block_Adminhtml_Renderer_Customer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
    public function render(Varien_Object $recurring)
    {
        $customer = Mage::getModel('customer/customer')->load($recurring->getCustomerId());
//        $name = $customer->getFirstname().' '.$customer->getLastname();
//        var_dump($row->getData());
        
        $html = '<a ';
        $html .= 'id="customer_' . $this->getColumn()->getId() . '" ';
        $html .= 'href="'.Mage::helper("adminhtml")->getUrl("adminhtml/customer/edit/id/".$customer->getId()."/").'"/>';
        $html .= $recurring->getShippingAddressObj()->format('html');
        $html .= '</a><br/>';
        return $html;
    }
}