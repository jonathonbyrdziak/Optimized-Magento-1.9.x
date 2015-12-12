<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MyorderController
 *
 * @author Jonathon
 */
class Widgetized_Recorder_OrdersController extends Mage_Core_Controller_Front_Action {
    /**
     * Function catches a call to place the order and attempts to fire the order
     */
    public function placeOrderAction() {
        ignore_user_abort(true);
        ini_set('ignore_user_abort',1);
        set_time_limit(86400);
        ini_set('max_execution_time',300);
        date_default_timezone_set('America/Los_Angeles');
        header("Connection: close");
        
        ob_start();
        $order_id = Mage::app()->getRequest()->getParam('order_id');
        
        $return = array(
            'result' => Mage::helper('recorder')->place_recurring_order($order_id),
            'order_id' => $order_id
        );
        Mage::log($return,null,'place_recurring_orders.log');
        
        ob_clean();
//        header('Content-type: application/json');
//        echo json_encode($return);
//        die;
    }
    
    /**
     * 
     */
    public function isAuthorized() {
        if (!Mage::helper('customer')->isLoggedIn()) {
            header('Location: '. Mage::getUrl() );
            die;
        }
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    /**
     * Display a list of Recurring Orders for the given user
     * 
     */
    public function indexAction() {
        $this->isAuthorized();
        $this->loadLayout();
        $this->renderLayout();
    }
    
    
    public function deleteitemAction() {
        $this->isAuthorized();
        
        $h = Mage::helper('recorder');
        
        $recurring = Mage::getModel('recorder/order');
        if ($recurring_id = $h->getParam('recurring_id')) {
            $recurring->load($recurring_id);
        }
        $skuCount = $recurring->deleteSku($h->getParam('item_sku'));
        
        if ($skuCount) {
            $recurring->save();
            header('Location: '. $h->editOrderUrl($recurring, true) );
        } else {
            $recurring->delete();
            header('Location: '. Mage::getUrl('recurring/orders') );
        }
        die;
    }
    
    /**
     * 
     * @return boolean
     */
    public function deleteAction() {
        $this->isAuthorized();
        
        $h = Mage::helper('recorder');
        if (!$h->getParam('deleteorder', false)) return false;
        
        $recurring = Mage::getModel('recorder/order');
        if ($recurring_id = $h->getParam('recurring_id')) {
            $recurring->load($recurring_id);
        }
        $recurring->delete();
        header('Location: '. Mage::getUrl()."/recurring/orders/" );
        die;
    }
    
    /**
     * 
     */
    public function viewAction() {
        $this->isAuthorized();
        $h = Mage::helper('recorder');
        
        $recurring = Mage::getModel('recorder/order');
        if ($recurring_id = $h->getParam('recurring_id')) {
            $recurring->load($recurring_id);
        }
        
        // bind the sales order to the recurring order
        if ($order_id = $h->getParam('order_id')) {
            $recurring->bind($order_id);
        }
        Mage::unregister('recurring_order');
        Mage::register('recurring_order', $recurring);
        
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * 
     */
    public function updateAction() {
        $this->isAuthorized();
        $h = Mage::helper('recorder');
        
        $recurring = Mage::getModel('recorder/order');
        if ($recurring_id = $h->getParam('recurring_id')) {
            $recurring->load($recurring_id);
        }
        
        $data = Mage::helper('recorder')->getParam('skus');
        $recurring->updateProducts($data);
        
        $recurring->setData('shipping_address',Mage::helper('recorder')->getParam('shipping_address'));
        $recurring->setData('billing_address',Mage::helper('recorder')->getParam('billing_address'));
        $recurring->setData('failed_attempt',Mage::helper('recorder')->getParam('failed_attempt'));
        $recurring->setData('reminder_sent',Mage::helper('recorder')->getParam('reminder_sent'));
        $recurring->setData('errors',Mage::helper('recorder')->getParam('errors'));
        $recurring->setData('enabled',Mage::helper('recorder')->getParam('enabled'));
        $recurring->setData('interval',Mage::helper('recorder')->getParam('interval'));
        $recurring->setData('start_date',Mage::helper('recorder')->getParam('start_date'));
        
        // checks before saving
        $results = Mage::helper('recorder')->dateHasPassed($recurring->getData('start_date'));
        if ($results) {
            $recurring->setData('start_date',  date(Widgetized_Recorder_Helper_Data::DATE_FORMAT, time()));
        }
        
        // gets saved in here
        $recurring->updateTotals(true);
        
        // brand new
        $message = (Mage::app()->getRequest()->getParam('new',false))
            ? $this->__("Recurring order #".$recurring->getId()." was created.")
            : $message = $this->__("Recurring order #".$recurring->getId()." updated.");
        Mage::getSingleton('core/session')->addSuccess($message);
        
        $redirect = (Mage::app()->getRequest()->getParam('finish',false))
                ? Mage::getUrl('recurring/orders')
                : $h->editOrderUrl($recurring, true);
        $this->_redirectUrl( $redirect );
    }
}
