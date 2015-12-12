<?php
/**
 * By Jonathon Byrd http://widgetized.co Copyright 2014. All Rights Reserved.
 * 
 */

class Widgetized_Level3_Helper_Paytrace extends Mage_Paygate_Helper_Data
{
    const SEPARATOR         = '|';
    const EQUALS            = '~';
    const URL = "https://paytrace.com/api/default.pay";
    
    /**
     * 
     * @return string
     */
    public function getCode() {
        return 'revolution';
    }
    
    /**
     * 
     * @param type $field
     * @param type $storeId
     * @return type
     */
    public function getConfigData($field, $storeId = null) {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore()->getStoreId();
        }
        $path = 'payment/'.$this->getCode().'/'.$field;
        return Mage::getStoreConfig($path, $storeId);
    }
    
    /**
     * EITHER CREATE OR UPDATE THE CUSTOMER
     * 
     * TO CREATE
     * http://help.paytrace.com/api-create-customer-profile
     * 
     * TO UPDATE
     * http://help.paytrace.com/api-update-customer-profile
     * 
     * TO DELETE
     * if $card is empty then we delete the card from paytrace
     * 
     * @param Mage_Customer_Model_Customer $customer
     */
    public function paytraceCustomer( Mage_Customer_Model_Customer $customer, Widgetized_Level3_Model_Card $card = null ) {
        $defaults = array(
            'UN'        => $this->getConfigData('login'), 
            'PSWD'      => $this->getConfigData('trans_key'), 
            'TERMS'     => 'Y', 
            'METHOD'    => 'CreateCustomer', 
            'CUSTID'    => $customer->getId(), 
            'BNAME'     => $customer->getFirstname().' '.$customer->getLastname(),
            'CC'        => '',
            'EXPMNTH'   => '',
            'EXPYR'     => '',
        );
        
        $addressId = $customer->getDefaultBilling();
        $billing = Mage::getModel('sales/order_address')->load($addressId);
        if ($billing) {
            $defaults['BADDRESS']   = $billing->getPostcode();
            $defaults['BCITY']      = $billing->getCity();
            $defaults['BSTATE']     = $billing->getRegion();
            $defaults['BZIP']       = $billing->getPostcode();
            $defaults['BCOUNTRY']   = $billing->getCountryId();
        }
        
        if ($card) {
            $defaults['CC']         = $card->getNumber();
            $defaults['EXPMNTH']    = $card->getMonth();
            $defaults['EXPYR']      = $card->getYear();
        }
        
        try {
            $result = $this->call(array_keys($defaults), $defaults);
        } catch (Exception $ex) {
            $message = $this->__($ex->getMessage());
            if ($card) {
                $card->setToken( $message );
                $card->save();
            }
        }
    }
    
    /**
     * 
     * @param type $defaults
     * @param type $data
     */
    public function call($defaults, $data) {
        try {
            $parmlist = $this->_createParmList($defaults, $data);
            $result = $this->_call($parmlist);
            
        } catch (Exception $ex) {

            if ($data['METHOD'] == 'CreateCustomer') {
                $data['METHOD'] = 'UpdateCustomer';
                $parmlist = $this->_createParmList($defaults, $data);
                $result = $this->_call($parmlist);
                return true;
            }
            
            throw new Exception($ex->getMessage());
        }
        return true;
    }
    
    /**
     * 
     * @param array $data
     * @return type
     */
    public function _createParmList( array $defaults, array $data ) {
        $params = array();
        foreach ($defaults as $default) {
            if (!array_key_exists($default, $data)) continue;
            $params[] = $default.self::EQUALS.$data[$default];
        }
        
        return "parmlist=".urlencode(implode(self::SEPARATOR, $params));
    }
    
    /**
     * 
     * 
     * @return type
     */
    public function _call($parmlist) {
        $header = array("MIME-Version: 1.0", "Content-type: application/x-www-form-urlencoded", "Contenttransfer-encoding: text");

        $ch = curl_init();
//        Mage::log('$parmlist', null, 'Paytrace.log');
//        Mage::log($parmlist, null, 'Paytrace.log');

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, self::URL);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parmlist);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        // grab URL and pass it to the browser
        $response = curl_exec($ch);

        // close curl resource, and free up system resources
        curl_close($ch);

        //parse through the response.
        $_response = array();
        $responseArr = explode('|', $response);
        foreach ($responseArr as $pair) {
            $tmp = explode('~', $pair);
            $_response[$tmp[0]] = $tmp[1];
        }
        
//        Mage::log('$response', null, 'Paytrace.log');
//        Mage::log($response, null, 'Paytrace.log');
//        Mage::log('$_response', null, 'Paytrace.log');
//        Mage::log($_response, null, 'Paytrace.log');
        
        if (isset($_response['ERROR'])) {
            throw new Exception( $_response['ERROR'] );
            
        } elseif (isset($_response['RESPONSE'])) {
            return $_response;
        }
        
//        Mage::log($_response,null,'paytrace.log');
        throw new Exception( 'No Response from The Payment Gateway' );
        return false;
    }

}