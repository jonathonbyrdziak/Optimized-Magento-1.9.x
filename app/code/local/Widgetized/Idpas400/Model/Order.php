<?php

class Widgetized_Idpas400_Model_Order extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('idpas400/order');
    }

    public function deleteByOrder($order_id, $var) {
        $this->_getResource()->deleteByOrder($order_id, $var);
    }

    public function getByOrder($order_id, $var = '') {
        return $this->_getResource()->getByOrder($order_id, $var);
    }

}
