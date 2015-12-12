<?php

class Widgetized_Level3_Model_Observer {
    
    protected $_attributes = array(
        'po_number'          => 'order.po_number',
//        'is_recurring'       => 'recorder.is_recurring',
//        'recurring_start_date' => 'recorder.start_date',
//        'customer_id'        => 'recorder.customer_id',
//        'recurring_interval' => 'recorder.recurring_interval',
        'use_saved_card'     => 'recorder.use_saved_card',
        'parent_id'          => 'recorder.parent_id',
//        'failed_attempt'     => 'recorder.failed_attempt',
//        'reminder_sent'      => 'recorder.reminder_sent'
    );
    
    /**
     * 
     */
    public function __construct() {
        $this->_attributes['is_recurring'] = 'options.'.
                Widgetized_Recorder_Helper_Data::OPTIONSID.'.is_recurring';
        
        $this->_attributes['recurring_start_date'] = 'options.'.
                Widgetized_Recorder_Helper_Data::OPTIONSID.'.start_date';
    }
    
    /**
     * This function is called just before $quote object get stored to database.
     * Here, from POST data, we capture our custom field and put it in the 
     * quote object
     * 
     * @param unknown_type $evt
     */
    public function saveQuoteBefore($evt) {
        $quote = $evt->getQuote();
        $post = Mage::app()->getFrontController()->getRequest()->getPost();
        
        foreach ($this->_attributes as $property => $_postProperty) {
            $parts = explode('.', $_postProperty);
            $value = null;
            $_post = $post;
            foreach ($parts as $part) {
                if (isset($_post[$part])) {
                    $_post = $_post[$part];
                    $value = $_post;
                }
            }
            if ($value !== null) {
                if (strpos($value, 'date')) {
                    $value = strtotime($value);
                }
                $quote->setData($property, $value);
            }
        }
    }

    /**
     * 
     * This function is called, just after $quote object get saved to database.
     * Here, after the quote object gets saved in database
     * we save our custom field in the our table created i.e sales_quote_custom
     * @param unknown_type $evt
     */
    public function saveQuoteAfter($evt) {
        $quote = $evt->getQuote();
        foreach ($this->_attributes as $property => $_postProperty) {
            if ($quote->getData($property)) {
                $value = $quote->getData($property);
                if (!empty($value)) {
                    $model = Mage::getModel('level3/quote');
                    $model->deleteByQuote($quote->getId(), $property);
                    $model->setQuoteId($quote->getId());
                    $model->setKey($property);
                    $model->setValue($value);
                    $model->save();
                }
            }
        }
    }

    /**
     *
     * When load() function is called on the quote object,
     * we read our custom fields value from database and put them back in 
     * quote object.
     * 
     * @param unknown_type $evt
     */
    public function loadQuoteAfter($evt) {
        $quote = $evt->getQuote();
        $model = Mage::getModel('level3/quote');
        $data = $model->getByQuote($quote->getId());
        foreach ($data as $key => $value) {
            $quote->setData($key, $value);
        }
    }

    /**
     * This function is called after order gets saved to database.
     * Here we transfer our custom fields from quote table to order table 
     * i.e sales_order_custom
     * 
     * @param $evt
     */
    public function convertQuoteToOrder($evt) {
        $order = $evt->getOrder();
        $quote = $evt->getQuote();
        
        foreach ($this->_attributes as $property => $_postProperty) {
            if ($quote->getData($property)) {
                $value = $quote->getData($property);
                if (!empty($value)) {
                    $order->setData($property, $value);
                    
                    $model = Mage::getModel('level3/order');
                    $model->deleteByOrder($order->getId(), $property);
                    $model->setOrderId($order->getId());
                    $model->setKey($property);
                    $model->setValue($value);
                    $model->save();
                }
            }
        }
    }

    /**
     * This function is called after order gets saved to database.
     * Here we transfer our custom fields from quote table to order table 
     * i.e sales_order_custom
     * 
     * @param $evt
     */
    public function saveOrderAfter($evt) {
        $order = $evt->getOrder();
        foreach ($this->_attributes as $property => $_postProperty) {
            if ($order->getData($property)) {
                $value = $order->getData($property);
                
                if (!empty($value)) {
                    $model = Mage::getModel('level3/order');
                    $model->deleteByOrder($order->getId(), $property);
                    $model->setOrderId($order->getId());
                    $model->setKey($property);
                    $model->setValue($value);
                    $model->save();
                }
            }
        }
    }

    /**
     *
     * This function is called when $order->load() is done.
     * Here we read our custom fields value from database and set it in order object.
     * @param unknown_type $evt
     */
    public function loadOrderAfter($evt) {
        $order = $evt->getOrder();
        $model = Mage::getModel('level3/order');
        $data = $model->getByOrder( $order->getId() );
        foreach ($data as $key => $value) {
            $order->setData($key, $value);
        }
    }

}
