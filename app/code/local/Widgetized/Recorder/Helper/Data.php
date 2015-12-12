<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Data
 *
 * @author Jonathon
 */
class Widgetized_Recorder_Helper_Data extends Mage_Core_Helper_Abstract {
    
    const OPTIONSID = 'recurring_order-245';
    const XML_PATH_EMAIL_NOTIFICATION_TEMPLATE = '13';
    const DATE_FORMAT = 'm/d/Y';
    const JS_DATE_FORMAT = 'mm/dd/yy';
    const REMINDER_DAYS_BEFORE_LARGE = 5;
    const REMINDER_DAYS_BEFORE_SMALL = 1;
    const shippingMethod = 'ups_03';
    const PAYMENTMETHOD = 'revolution_saved';
    
    /**
     * 
     * @param type $order
     * @return type
     */
    public function createOrderUrl( $order, $recurring = false ) {
        $url = Mage::getUrl('recurring/orders/view');
        if ($recurring) {
            if (!is_object($order)) $order = Mage::getModel('recorder/order')->load($order);
            $url .= '?options['.self::OPTIONSID.'][recurring_id]='.$order->getId();
        } else {
            if (!is_object($order)) $order = Mage::getModel('sales/order')->load($order);
            $url .= '?options['.self::OPTIONSID.'][order_id]='.$order->getId();
        }
        $url .= '&options['.self::OPTIONSID.'][createorder]=1';
        
        return $url;
    }
    
    /**
     * 
     * @param type $order
     * @return string
     */
    public function editOrderUrl( $order, $recurring = false ) {
        $url = Mage::getUrl('recurring/orders/view');
        if ($recurring) {
            if (!is_object($order)) $order = Mage::getModel('recorder/order')->load($order);
            $url .= '?options['.self::OPTIONSID.'][recurring_id]='.$order->getId();
        } else {
            if (!is_object($order)) $order = Mage::getModel('sales/order')->load($order);
            $url .= '?options['.self::OPTIONSID.'][order_id]='.$order->getId();
        }
        $url .= '&options['.self::OPTIONSID.'][editorder]=1';
        
        return $url;
    }
    
    public function getDeleteItemUrl( $item ) {
        $url = Mage::getUrl('recurring/orders/deleteitem');
        $url .= '?options['.self::OPTIONSID.'][recurring_id]='.Mage::registry('recurring_order')->getId();
        $url .= '&options['.self::OPTIONSID.'][item_sku]='.$item->getSku();
        return $url;
    }
    
    /**
     * 
     * @param type $order
     * @return string
     */
    public function cancelOrderUrl( $order, $recurring = false ) {
        $url = Mage::getUrl('recurring/orders/delete');
        if ($recurring) {
            $url .= '?options['.self::OPTIONSID.'][recurring_id]='.$order->getId();
        } else {
            $url .= '?options['.self::OPTIONSID.'][order_id]='.$order->getId();
        }
        $url .= '&options['.self::OPTIONSID.'][deleteorder]=1';
        
        $url .= '" onClick="return confirm(\'Are you sure that you want to delete this order?\');';
        return $url;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getCustomerId() {
        if (!Mage::helper('customer')->isLoggedIn()) return false;
        $customerObj = Mage::getSingleton('customer/session')->getCustomer();
        return $customerObj->getId();
    }
    
    /**
     * 
     * @return type
     */
    public function getAllSubscriptions() {
        $recurringCollection = Mage::getModel('recorder/order')->getCollection();
        return $recurringCollection;
    }
    
    /**
     * 
     * @param type $property
     * @param type $default
     * @return type
     */
    public function getParam( $property, $default = null ) {
        $options = Mage::app()->getRequest()->getParam('options',array());
        
        if (!isset($options[self::OPTIONSID])) return $default;
        if (!isset($options[self::OPTIONSID][$property])) return $default;
        return $options[self::OPTIONSID][$property];
    }
    
    /**
     * 
     * @param type $property
     * @param type $default
     * @return type
     */
    public function getParams() {
        $options = Mage::app()->getRequest()->getParam('options',array());
        
        if (!isset($options[self::OPTIONSID])) return array();
        return $options[self::OPTIONSID];
    }
    
    /**
     * 
     * @param type $date
     */
    public function dateIsDaysBefore( $date ) {
        
        $datetime1 = date_create($date);
        $datetime2 = date_create(date('m/d/Y'));
        $interval = date_diff($datetime1, $datetime2);
        
        if (strtotime($date) > time()
        && $interval->days <= self::REMINDER_DAYS_BEFORE_LARGE 
        && $interval->days >= self::REMINDER_DAYS_BEFORE_SMALL) return true;
        return false;
    }
    
    /**
     * 
     * @param type $date
     */
    public function dateHasPassedOrIsToday( $date ) {
        // if nextTime is less than today, then it has passed
        if (strtotime($date) <= time()) return true;
        return false;
    }
    
    /**
     * 
     * @param type $date
     */
    public function dateHasPassed( $date ) {
        
        $datetime1 = date_create($date);
        $datetime2 = date_create(date('m/d/Y'));
        $interval = date_diff($datetime1, $datetime2);
        
        if ($interval->days>=1 && strtotime($date) < time()) return true;
        return false;
    }
    
    /**
     * 
     * @return type
     */
    public function getSubscriptions( $enabled = 1 ) {
//        $customer = Mage::getSingleton('customer/session')->getCustomer();
        
        $recurringCollection = Mage::getModel('recorder/order')->getCollection();
        if ($enabled) {
                $recurringCollection->addFieldToFilter('enabled', array('eq' => $enabled));
        }
        $recurringCollection->load();
        
        return $recurringCollection;
    }
    
    /**
     * This method will loop through ever order and fire those that are ready
     */
    public function place_recurring_orders() {
        ignore_user_abort(true);
        ini_set('ignore_user_abort',1);
        set_time_limit(86400);
        ini_set('max_execution_time',86400);
        date_default_timezone_set('America/Los_Angeles');
        
        ob_start();
        // placing the orders
        $placedOrders = array();
        foreach($this->getSubscriptions() as $recurring) {
            if (in_array($recurring->getId(), $placedOrders)) continue;
            
            if ($error = $recurring->has_errors()) {
                echo "<br/>".date('Y-m-d').": Order #{$recurring->getId()} says: $error";
                continue;
            }
            
            // place the order and continue
            if ($recurring->canProcess()) {
                $results = $recurring->placeOrder();
//                echo "<br/>".date('Y-m-d').": ".$this->curl_recurring_order( $recurring->getId() );

                if ($results === true) {
                    $placedOrders[] = $recurring->getId();
                } else {
                    echo '<br/>'.$results;
                }
                continue;
            }
            
            if ($recurring->canSendReminder()) {
                echo "<br/>".date('Y-m-d').": Order #{$recurring->getId()} sending email reminder";
                $this->sendEmailNotification($recurring);
                $recurring->setData('reminder_sent', true);
                $recurring->save();
            }
        }
        
        echo "<br/>".date('Y-m-d').": ".count($placedOrders)." Placed Orders";
        Mage::log(ob_get_contents(),null,'place_recurring_orders.log');
    }
    
    /**
     * 
     * Function curls Magento to trigger a recurring order to be placed.
     * 
     * @param type $order_id
     */
    public function curl_recurring_order( $order_id ) {
        $session = curl_init();
 
        $url = Mage::getUrl()."recurring/orders/placeOrder?order_id=$order_id";

        try {
            curl_setopt($session, CURLOPT_URL, $url);
            curl_setopt($session, CURLOPT_HTTPGET, 1);
            curl_setopt($session, CURLOPT_HEADER, false);
            curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_TIMEOUT, 1); 

            curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($session);
            curl_close($session);
        } catch (Exception $e) {
            $responseObject = array();
            $responseObject['result'] = 'Exception Caught: '.$e->getMessage();
        }
        
        if (!$response) {
            $responseObject = array('result' => "Pinged order # $order_id at $url");
        } else {
            $responseObject = json_decode($response, true);
        }
        
        if (isset($responseObject['result'])) {
            return $responseObject['result'];
        }
        return false;
    }
    
    /**
     * Method to fire a Singular recurring order
     * 
     * @param type $order_id
     * @return boolean
     */
    public function place_recurring_order( $order_id = false ) {
        if (!$order_id) return false;
        $recurring = Mage::getModel('recorder/order')->load($order_id);
        
        if (!$recurring->getId()) return false;
        if ($error = $recurring->has_errors()) {
            return "Order #{$recurring->getId()} says: $error";
        }
        
        // place the order and continue
//        if ($recurring->canProcess()) {
            return $recurring->placeOrder();
//        }
        
        if ($recurring->canSendReminder()) {
            $this->sendEmailNotification($recurring);
            $recurring->setData('reminder_sent', true);
            $recurring->save();
            return "Reminder Email Sent";
        }
        
        return "Nothing to do";
    }
    
    /**
     * 
     * @param type $order
     */
    public function sendEmailNotification( $recurring, $emailOverride = false ) {
        
        $storeId = $recurring->getStoreId();
        
        if (!Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return $this;
        }
        
        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
        
        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name XML_PATH_EMAIL_NOTIFICATION_TEMPLATE
        $templateId = self::XML_PATH_EMAIL_NOTIFICATION_TEMPLATE;
        $customerName = $recurring->getCustomerName();

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo( $emailOverride?$emailOverride:$recurring->getCustomer()->getEmail(), $customerName);
        $mailer->addEmailInfo($emailInfo);

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        
        $recurring->getQuote()->setRecurring($recurring);
                
        $mailer->setTemplateParams(array(
                'recurring'    => $recurring,
                'quote'        => $recurring->getQuote(),
                'billing'      => $recurring->getBillingAddressObj(),
                'payment'      => $recurring->getCreditcard()
            )
        );
        
        $mailer->send();
        
        return $recurring;
    }
}
