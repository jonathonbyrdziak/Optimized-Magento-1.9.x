<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CartController
 *
 * @author Jonathon
 */
class Widgetized_Idpas400_PingController extends Mage_Core_Controller_Front_Action {

    /**
     * https://www.b4schools.com/externaldb/ping?erporderid=
     * 
     * 
     */
    public function indexAction() {
        $orderid = Mage::app()->getRequest()->getParam('erporderid');
        $response = Mage::helper('idpas400')->updateOrderStatusFromErp($orderid);
        $this->_echoJson(array(
            'response' => $response
        ));
    }
    
    /**
     * 
     * @param type $response
     */
    public function _echoJson( $response ) {
        echo json_encode($response);
        die;
    }
}
