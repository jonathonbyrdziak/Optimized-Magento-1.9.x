<?php

class Widgetized_Level3_Model_Resource_Card_Collection 
    extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('level3/card');
    }

}
