<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Order
 *
 * @author Jonathon
 */
class Widgetized_Recorder_Block_Account_Order extends Widgetized_Recorder_Block_Abstract {
    

    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Order # %s', $this->getRecurring()->getId()));
        }
    }
    
    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getQuote()
    {
        return $this->getRecurring()->getQuote();
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::getUrl('/');
        }
        return $this->url('orders');
    }

    public function getRecurring() {
        return Mage::registry('recurring_order');
    }
}
