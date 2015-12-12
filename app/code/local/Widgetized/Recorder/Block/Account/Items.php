<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'Mage'.DIRECTORY_SEPARATOR.'Checkout'.DIRECTORY_SEPARATOR.'Block'.DIRECTORY_SEPARATOR.'Cart.php';
class Widgetized_Recorder_Block_Account_Items extends Mage_Checkout_Block_Cart
{
    /**
     * Get active quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getRecurring()->getQuote();
    }

    public function getRecurring()
    {
        return Mage::registry('recurring_order');
    }
}