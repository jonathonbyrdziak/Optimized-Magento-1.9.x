<?php

class Widgetized_Level3_Model_Resource_Card extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('level3/card', 'id');
    }
}
