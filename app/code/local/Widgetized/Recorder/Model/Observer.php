<?php

/**
 * Description of Observer
 *
 * @author Jonathon
 */
class Widgetized_Recorder_Model_Observer {
    
    /**
     * Find out if we have any orders that need to go out
     * 
     * pulled from recorder_recurring_orders table
     */
    public function cron_check_order() {
        Mage::helper('recorder')->place_recurring_orders();
    }
}
