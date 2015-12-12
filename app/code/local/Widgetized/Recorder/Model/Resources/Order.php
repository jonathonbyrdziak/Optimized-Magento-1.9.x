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
class Widgetized_Recorder_Model_Resources_Order extends Mage_Core_Model_Mysql4_Abstract {
    //put your code here
    public function _construct()
    {
        $this->_init('recorder/order', 'id');
    }
}
