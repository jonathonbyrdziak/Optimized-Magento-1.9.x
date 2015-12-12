<?php

class Widgetized_Level3_Model_Order extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('level3/order');
    }
    
    public function deleteByOrder($order_id, $var) {
        $this->_getResource()->deleteByOrder($order_id, $var);
    }
    
    public function deleteOrder($order_id) {
        return $this->_getResource()->deleteOrder($order_id);
    }
    
    public function getByOrder($order_id, $var = '') {
        return $this->_getResource()->getByOrder($order_id, $var);
    }
    
    public function getByOrderMeta($prop, $val='') {
        return $this->_getResource()->getByOrderMeta($prop, $val);
    }
}
