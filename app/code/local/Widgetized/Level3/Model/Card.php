<?php

class Widgetized_Level3_Model_Card extends Mage_Core_Model_Abstract {
    /**
     * 
     */
    public function _construct() {
        parent::_construct();
        $this->_init('level3/card');
    }
}
