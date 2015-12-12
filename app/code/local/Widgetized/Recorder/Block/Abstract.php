<?php
/**
 * 
 */

/**
 * 
 */
class Widgetized_Recorder_Block_Abstract extends Mage_Core_Block_Template {
    
    var $intervals = array(
        '3 month' => 'Every Quarter',
        '1 month' => 'Every 30 Days',
        '2 week'  => 'Every 2 Weeks',
        '1 week'  => 'Every Week',
//        '1 day'   => 'Every Day',
//        '5 minutes'   => 'Every 5 Minutes'
    );
    
    /**
     * 
     * @param type $interval
     */
    public function getInterval( $interval ) {
        return $this->intervals[$interval];
    }
    
    
    public function getCustomer() {
        $model = $this->getQuote();
        $customer = $model->getCustomer();
        if (!$customer) {
            $customer = Mage::getModel('customer/customer')
                ->load( $model->getCustomerId() );
        }
        return $customer;
    }
    
    /**
     * 
     * @return type
     */
    public function getSubscriptions() {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        
        $recurringCollection = Mage::getModel('recorder/order')->getCollection()
                ->addFieldToFilter('customer_id', array('eq' => $customer->getId()))
                ->addFieldToFilter('enabled', array('eq' => 1))
                ->load();
        
        return $recurringCollection;
    }
    
    /**
     * 
     * @param type $productImage
     * @return type
     */
    public function getImageUrl( $_product, $size = 100 ) {
        $url = $this->helper('catalog/image')->init($_product, 'small_image')->resize($size);
        return $url;
    }
    
    /**
     * 
     * @param type $property
     */
    public function getOptions( $property = NULL, $default = false ) {
        if (!isset($this->$property)) return;
        
        foreach ($this->$property as $value => $text) {
            $option = '';
            $option .= '<option ';
            if ($default == $value) {
                $option .= ' selected="selected" ';
            }
            $option .= 'value="'.$value.'">'.$text.'</option>';
            echo $option;
        }
    }
    
    /**
     * 
     * @param type $path
     */
    public function url( $path = '' ) {
        echo Mage::getBaseUrl()."recurring/orders/$path";
    }
}