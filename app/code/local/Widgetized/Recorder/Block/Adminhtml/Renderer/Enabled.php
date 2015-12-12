<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
// .grid-severity-critical // red
// .grid-severity-notice //green
// .blue .bar-lightblue .bar-red, .bar-orange, .bar-yellow, .bar-green, 
// .bar-lightblue, .bar-blue, .bar-lightgray

class Widgetized_Recorder_Block_Adminhtml_Renderer_Enabled extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
    /**
     * 
     * @param Varien_Object $recurring
     * @return type
     */
    public function render(Varien_Object $recurring)
    {
        $helper = Mage::helper('recorder');
        
        if (!$recurring->isEnabled()) {
            echo '<span class="bar-lightgray"><span>Disabled</span></span>';
            return;
        }
        
        if ($msg = $recurring->has_errors()):
            echo '<span class="grid-severity-critical"><span>Has Errors</span></span>';
            return;
        endif;
        
        if ($recurring->canProcess()) {
            echo '<span class="grid-severity-notice"><span>Firing Next Run</span></span>';
            return;
        }
        
        if ($recurring->getData('reminder_sent')) {
            echo '<span class="bar-lightblue"><span>Reminder Sent</span></span>';
        } elseif ($recurring->canSendReminder()) {
            echo '<span class="bar-lightblue"><span>Reminder will be sent</span></span>';
        }
        
        echo '<span class="grid-severity-notice"><span>Waiting to Process</span></span>';
    }
}