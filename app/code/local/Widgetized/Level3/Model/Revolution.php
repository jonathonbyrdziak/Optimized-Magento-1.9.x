<?php

require_once 'Mage/Paygate/Model/Authorizenet.php';

class Widgetized_Level3_Model_Revolution extends Mage_Paygate_Model_Authorizenet
{
    /*
     * AIM gateway url
     */
    const CGI_URL = 'https://PayTrace.com/API/gateway.pay';
//    const CGI_URL = 'https://PayTrace.com/API/default.pay';

    /*
     * Transaction Details gateway url
     */
    const CGI_URL_TD = 'https://PayTrace.com/API/gateway.pay';
//    const CGI_URL_TD = 'https://PayTrace.com/API/default.pay';

    const RESPONSE_DELIM_CHAR = ',';
    
    const METHOD_CODE = 'revolution';
    protected $_code  = 'revolution';
    
    /**
     * Form block type
     */
    protected $_formBlockType = 'level3/revolution_cc';
    
    /**
     * Enable this for debugging
     * @return boolean
     */
//    public function getDebugFlag(){return true;}
    
    /**
     * This method _buildRequest and then _postRequest
     */
//    public function _place

    /**
     * Prepare request to gateway
     * 
     * HERE WE NEED TO CHIME IN AND USE AN EXISTING CUSTOMER ACCOUNT IF A TOKEN
     * IS PRESENT
     *
     * @link http://www.authorize.net/support/AIM_guide.pdf
     * @param Mage_Payment_Model_Info $payment
     * @return Mage_Paygate_Model_Authorizenet_Request
     */
    protected function _buildRequest(Varien_Object $payment)
    {
        $order = $payment->getOrder();

        $this->setStore($order->getStoreId());

        $request = $this->_getRequest()
            ->setXType($payment->getAnetTransType())
            ->setXMethod(self::REQUEST_METHOD_CC);

        if ($order && $order->getIncrementId()) {
            $request->setXInvoiceNum($order->getIncrementId());
        }
        
        if($payment->getAmount()){
            $request->setXAmount($payment->getAmount(),2);
            $request->setXCurrencyCode($order->getBaseCurrencyCode());
        }

        switch ($payment->getAnetTransType()) {
            case self::REQUEST_TYPE_AUTH_CAPTURE:
                $request->setXAllowPartialAuth($this->getConfigData('allow_partial_authorization') ? 'True' : 'False');
                if ($payment->getAdditionalInformation($this->_splitTenderIdKey)) {
                    $request->setXSplitTenderId($payment->getAdditionalInformation($this->_splitTenderIdKey));
                }
                break;
            case self::REQUEST_TYPE_AUTH_ONLY:
                $request->setXAllowPartialAuth($this->getConfigData('allow_partial_authorization') ? 'True' : 'False');
                if ($payment->getAdditionalInformation($this->_splitTenderIdKey)) {
                    $request->setXSplitTenderId($payment->getAdditionalInformation($this->_splitTenderIdKey));
                }
                break;
            case self::REQUEST_TYPE_CREDIT:
                /**
                 * Send last 4 digits of credit card number to authorize.net
                 * otherwise it will give an error
                 * 
                 * x_trans_id is the transaction ID we provide every 
                 * transaction. It would be used to reference transactions in 
                 * our system when doing export requests, etc.
                 * 
                 */
                $request->setXCardNum($payment->getCcLast4());
                $request->setXTransId($payment->getXTransId());
                break;
            case self::REQUEST_TYPE_VOID:
                $request->setXTransId($payment->getXTransId());
                break;
            case self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
                $request->setXTransId($payment->getXTransId());
                break;
            case self::REQUEST_TYPE_CAPTURE_ONLY:
                /**
                 * x_auth_code is the authorization code you would pass if you 
                 * were doing a forced sale type where you already had an 
                 * approval and needed to force it into PayTrace.
                 */
                $request->setXAuthCode($payment->getCcAuthCode());
                break;
        }

        if ($this->getIsCentinelValidationEnabled()){
            $params  = $this->getCentinelValidator()->exportCmpiData(array());
            $request = Varien_Object_Mapper::accumulateByMap($params, $request, $this->_centinelFieldMap);
        }
        
        /**
         * x_description is a description you can pass along with the 
         * transaction which will show in PayTrace reporting for reporting 
         * purposes.
         */
        $request->setXDescription('Magento Store Online Order');

        if (!empty($order)) {
            $billing = $order->getBillingAddress();
            if (!empty($billing)) {
                $request
                    ->setXDelimChar(self::RESPONSE_DELIM_CHAR)
                    ->setXEncapChar('')
                    ->setXFirstName($billing->getFirstname())
                    ->setXLastName($billing->getLastname())
                    ->setXCompany($billing->getCompany())
                    ->setXAddress($billing->getStreet(1))
                    ->setXCity($billing->getCity())
                    ->setXState($billing->getRegion())
                    ->setXZip($billing->getPostcode())
                    //->setXCountry($billing->getCountry())
                    ->setXPhone($billing->getTelephone())
                    ->setXFax($billing->getFax())
                    //->setXCustId($order->getCustomerId())
                    //->setXCustomerIp($order->getRemoteIp())
                    //->setXCustomerTaxId($billing->getTaxId())
                    ->setXEmail($order->getCustomerEmail())
                    //->setXEmailCustomer($this->getConfigData('email_customer'))
                    ->setXMerchantEmail($this->getConfigData('merchant_email'))
                        ;
            }

            $shipping = $order->getShippingAddress();
            if (!empty($shipping)) {
                $request->setXShipToFirstName($shipping->getFirstname())
                    ->setXShipToLastName($shipping->getLastname())
                    ->setXShipToCompany($shipping->getCompany())
                    ->setXShipToAddress($shipping->getStreet(1))
                    ->setXShipToCity($shipping->getCity())
                    ->setXShipToState($shipping->getRegion())
                    ->setXShipToZip($shipping->getPostcode())
                    ->setXShipToCountry($shipping->getCountry());
            }

            /*
             * x_po_num - * For Authorize.net this field is for Purchase 
             * Order numbers, for PayTrace this is only used for 
             * transactions that are identified as corporate or purchasing 
             * credit cards. This is an identifier that your customer may 
             * ask you to provide in order to reference the transaction to 
             * their credit card statement.
             */
//e            $po_number = $order->getPoNumber()
//                        ?$order->getPoNumber()
//                        : ($order->getQuote()
//                               ? $order->getQuote()->getPoNumber()
//                                : '');
//            
//            $request->setXPoNum($po_number);
//                
//            $request->setXTax($order->getBaseTaxAmount())
//f               ->setXFreight($order->getBaseShippingAmount());
            
//            
//    //        Use these fields if we're using a stored credit card
//            $useSavedCard = $order->getUseSavedCard()
//                    ? $order->getUseSavedCard()
//                    : ($order->getQuote()
//                            ? $order->getQuote()->getUseSavedCard()
//                            : false);
//            if ($useSavedCard) {
//                // setXCustid
//                // setXCustomerProfileId
//                // setXCustomerPaymentProfileId
//                $request->setXCustId( $order->getCustomerId() );
//            }
        }

//        Use these fields if we're using a newly entered credit card
        if($payment->getCcNumber()){
            $request
                ->setXCardNum($payment->getCcNumber())
                ->setXExpDate(sprintf('%02d-%04d', 
                        $payment->getCcExpMonth(), 
                        $payment->getCcExpYear())
                   )
                ->setXCardCode($payment->getCcCid());
        }
        return $request;
    }

    /**
     * Post request to gateway and return responce
     *
     * @param Mage_Paygate_Model_Authorizenet_Request $request)
     * @return Mage_Paygate_Model_Authorizenet_Result
     */
    protected function _postRequest(Varien_Object $request)
    {
        $debugData = array('request' => $request->getData());
        $debugData['class'] = get_class($request);

        $result = Mage::getModel('paygate/authorizenet_result');

        $client = new Varien_Http_Client();

        $uri = $this->getConfigData('cgi_url');
        $client->setUri($uri ? $uri : self::CGI_URL);
        
//Mage::log(' ---------- new post request ', null, 'capture.log');
//Mage::log(' uri '.$client->getUri(), null, 'capture.log');
        
        $client->setConfig(array(
            'maxredirects'=>0,
            'timeout'=>30,
            //'ssltransport' => 'tcp',
        ));
        foreach ($request->getData() as $key => $value) {
            $request->setData($key, str_replace(self::RESPONSE_DELIM_CHAR, '', $value));
        }
        $request->setXDelimChar(self::RESPONSE_DELIM_CHAR);

        $client->setParameterPost($request->getData());
        $client->setMethod(Zend_Http_Client::POST);

//Mage::log($request->getData(), null, 'capture.log');


        try {
            $response = $client->request();
        } catch (Exception $e) {
            $result->setResponseCode(-1)
                ->setResponseReasonCode($e->getCode())
                ->setResponseReasonText($e->getMessage());

            $debugData['result'] = $result->getData();
            $this->_debug($debugData);
            Mage::throwException($this->_wrapGatewayError($e->getMessage()));
        }

        $responseBody = $response->getBody();
        $debugData['responseBody'] = $responseBody;
                
        $r = explode(self::RESPONSE_DELIM_CHAR, $responseBody);
        $debugData['deliminator'] = self::RESPONSE_DELIM_CHAR;
        $debugData['results_exploded'] = $r;
                
        if ($r) {
            $result->setResponseCode((int)str_replace('"','',$r[0]))
                ->setResponseSubcode((int)str_replace('"','',$r[1]))
                ->setResponseReasonCode((int)str_replace('"','',$r[2]))
                ->setResponseReasonText($r[3])
                ->setApprovalCode($r[4])
                ->setAvsResultCode($r[5])
                ->setTransactionId($r[6])
                ->setInvoiceNumber($r[7])
                ->setDescription($r[8])
                ->setAmount($r[9])
                ->setMethod($r[10])
                ->setTransactionType($r[11])
                ->setCustomerId($r[12])
                ->setMd5Hash($r[37])
                ->setCardCodeResponseCode($r[38])
                ->setCAVVResponseCode( (isset($r[39])) ? $r[39] : null)
                ->setSplitTenderId($r[52])
                ->setAccNumber($r[50])
                ->setCardType($r[51])
                ->setRequestedAmount($r[53])
                ->setBalanceOnCard($r[54])
                ;
        }
        else {
             Mage::throwException(
                Mage::helper('revolution')->__('Error in payment gateway.')
            );
        }

        $debugData['result'] = $result->getData();
        $this->_debug($debugData);

        return $result;
    }
}