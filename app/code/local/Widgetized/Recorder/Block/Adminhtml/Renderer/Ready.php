<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Widgetized_Recorder_Block_Adminhtml_Renderer_Ready extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
    public function render(Varien_Object $_order)
    {
        if (!$_order->isEnabled()) {
            return;
        }
        
        if ($msg = $_order->has_errors()): 
            echo $msg;
            return;
        endif;
        
        if ($_order->getData('reminder_sent')):
            ?>We have emailed you a reminder for this order.<?php
        endif;
        
        if (Mage::helper('recorder')->dateHasPassedOrIsToday($_order->getData('start_date'))):
            ?>Your order #<?php echo $_order->getId() ?> has passed all checks and is ready to be placed tonight!<?php 
            return;
        endif;
        
        if ($_order->canSendReminder()):
            ?>Tonight we'll be sending you a reminder email about this recurring order.<?php
        endif;
    }
}