<?php
/**
 * 
 */

/**
 * 
 */
class Widgetized_Recorder_Block_Verified extends Mage_Core_Block_Template {
    
    public function __construct(array $args = array()) {
        parent::__construct($args);
        $this->_template = 'customer/verified.phtml';
    }
    
    public function getCustomer() {
        return Mage::getSingleton('customer/session')->getCustomer();
    }
}