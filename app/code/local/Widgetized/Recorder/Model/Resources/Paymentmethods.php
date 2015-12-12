<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of History
 *
 * @author Jonathon
 */
class Widgetized_Recorder_Model_Resources_Paymentmethods extends Mage_Core_Model_Mysql4_Abstract {
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('recorder/paymentmethods','entity_id');
    }
}
