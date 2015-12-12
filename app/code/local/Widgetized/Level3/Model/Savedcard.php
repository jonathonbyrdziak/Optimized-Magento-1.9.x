<?php
/**
 * 
 */
require_once 'Mage/Paygate/Model/Authorizenet.php';

/**
 * 
 * @author anonymous
 *
 */
class Widgetized_Level3_Model_Savedcard extends Mage_Paygate_Model_Authorizenet
{
    /*
     * AIM gateway url
     */
    const CGI_URL = 'https://PayTrace.com/API/default.pay';

    /*
     * Transaction Details gateway url
     */
    const CGI_URL_TD = 'https://PayTrace.com/API/default.pay';

    const RESPONSE_DELIM_CHAR = ',';
    const REQUEST_METHOD_CC = 'ProcessTranx';
    
    const METHOD_CODE = 'revolution_saved';
    protected $_code  = 'revolution_saved';
    
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
     * Send request with new payment to gateway
     *
     * @param Mage_Payment_Model_Info $payment
     * @param decimal $amount
     * @param string $requestType
     * @return Mage_Paygate_Model_Authorizenet
     * @throws Mage_Core_Exception
     */
    protected function _place($payment, $amount, $requestType)
    {
        $payment->setAnetTransType($requestType);
        $payment->setAmount($amount);
        $request= $this->_buildRequest($payment);
        $result = $this->_postRequest($request);

        switch ($requestType) {
            case self::REQUEST_TYPE_AUTH_ONLY:
                $newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH;
                $defaultExceptionMessage = Mage::helper('paygate')->__('Payment authorization error.');
                break;
            case self::REQUEST_TYPE_AUTH_CAPTURE:
                $newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE;
                $defaultExceptionMessage = Mage::helper('paygate')->__('Payment capturing error.');
                break;
        }

        switch ($result->getResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
                $this->getCardsStorage($payment)->flushCards();
                $card = $this->_registerCard($result, $payment);
                $this->_addTransaction(
                    $payment,
                    $card->getLastTransId(),
                    $newTransactionType,
                    array('is_transaction_closed' => 0),
                    array($this->_realTransactionIdKey => $card->getLastTransId()),
                    Mage::helper('paygate')->getTransactionMessage(
                        $payment, $requestType, $card->getLastTransId(), $card, $amount
                    )
                );
                if ($requestType == self::REQUEST_TYPE_AUTH_CAPTURE) {
                    $card->setCapturedAmount($card->getProcessedAmount());
                    $this->getCardsStorage($payment)->updateCard($card);
                }
                return $this;
            case self::RESPONSE_CODE_HELD:
                if ($result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED
                    || $result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_PENDING_REVIEW
                ) {
                    $card = $this->_registerCard($result, $payment);
                    $this->_addTransaction(
                        $payment,
                        $card->getLastTransId(),
                        $newTransactionType,
                        array('is_transaction_closed' => 0),
                        array(
                            $this->_realTransactionIdKey => $card->getLastTransId(),
                            $this->_isTransactionFraud => true
                        ),
                        Mage::helper('paygate')->getTransactionMessage(
                            $payment, $requestType, $card->getLastTransId(), $card, $amount
                        )
                    );
                    if ($requestType == self::REQUEST_TYPE_AUTH_CAPTURE) {
                        $card->setCapturedAmount($card->getProcessedAmount());
                        $this->getCardsStorage()->updateCard($card);
                    }
                    $payment
                        ->setIsTransactionPending(true)
                        ->setIsFraudDetected(true);
                    return $this;
                }
                if ($result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_PARTIAL_APPROVE) {
                    $checksum = $this->_generateChecksum($request, $this->_partialAuthorizationChecksumDataKeys);
                    $this->_getSession()->setData($this->_partialAuthorizationChecksumSessionKey, $checksum);
                    if ($this->_processPartialAuthorizationResponse($result, $payment)) {
                        return $this;
                    }
                }
                Mage::throwException($defaultExceptionMessage);
            case self::RESPONSE_CODE_DECLINED:
            case self::RESPONSE_CODE_ERROR:
                Mage::throwException($this->_wrapGatewayError($result->getResponseReasonText()));
            default:
                Mage::throwException($defaultExceptionMessage);
        }
        return $this;
    }

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

        $request = $this->_getRequest()->setMethod(self::REQUEST_METHOD_CC);
        switch ($payment->getAnetTransType()) {
            case self::REQUEST_TYPE_AUTH_CAPTURE:
                $request->setTranxtype('Sale');
                
                break;
            case self::REQUEST_TYPE_AUTH_ONLY:
//                $request->setTranxtype('Authorization');
                $request->setTranxtype('Sale');
                
                break;
            case self::REQUEST_TYPE_CREDIT:
                $request->setTranxtype('Refund');
               
                break;
            case self::REQUEST_TYPE_VOID:
                $request->setTranxtype('Void');
                $request->setTranxid($payment->getXTransId());
                break;
            case self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
                $request->setTranxtype('Capture');
                
                break;
            case self::REQUEST_TYPE_CAPTURE_ONLY:
                $request->setTranxtype('Capture');
                break;
        }
        
        if (!empty($order)) {
            $billing = $order->getBillingAddress();
            if (!empty($billing)) {
                $request
                    ->setBname($billing->getFirstname().' '.$billing->getLastname())
                    ->setBaddress($billing->getStreet(1))
                    ->setBcity($billing->getCity())
                    ->setBstate($billing->getRegion())
                    ->setBzip($billing->getPostcode())
                    ->setBcountry($billing->getCountry())
                    ->setEmail($order->getCustomerEmail())
                        ;
            }

            $shipping = $order->getShippingAddress();
            if (!empty($shipping)) {
                $request->setSname($shipping->getFirstname().' '.$shipping->getLastname())
                    ->setSaddress($shipping->getStreet(1))
                    ->setScity($shipping->getCity())
                    ->setSstate($shipping->getRegion())
                    ->setSzip($shipping->getPostcode())
                    ->setScountry($shipping->getCountry())
                    ;
            }
            
            // loading the customer class
            $customer = $order->getCustomer();
            
            $request->setInvoice($order->getIncrementId())
                    ->setTax($order->getBaseTaxAmount())
                    ->setFreight($order->getBaseShippingAmount())
                    ->setCustid( $customer->getId() )
                    ;

            /*
             * x_po_num - * For Authorize.net this field is for Purchase 
             * Order numbers, for PayTrace this is only used for 
             * transactions that are identified as corporate or purchasing 
             * credit cards. This is an identifier that your customer may 
             * ask you to provide in order to reference the transaction to 
             * their credit card statement.
             */
            $po_number = $order->getPoNumber()
                        ? $order->getPoNumber()
                        : $order->getQuote()->getPoNumber();
            
            // LEVEL 3 DATA
            if ($po_number && $customer->getCustomerTaxId()) {
                $request->setCustref($po_number);
                $request->setCustomertaxid( $customer->getCustomerTaxId() );
                $request->setMerchanttaxid( $this->getConfigData('tax_id') );
                
                $request->setTax('-1');
                $request->setAddtaxrate('0');
                $request->setAddtax('0');
                $request->setDuty('0');
                $request->setDiscount('0');
                
                //Commodity code that generally applies to each product included
                //in the order. Commodity codes are generally assigned by your 
                //merchant service provider
//                $request->setCcode();
                
                $sourceZip = Mage::getStoreConfig('shipping/origin/postcode/', $this->getStore());
                $request->setSourcezip($sourceZip);
            }
            
        }

        /**
         * x_description is a description you can pass along with the 
         * transaction which will show in PayTrace reporting for reporting 
         * purposes.
         */
        $request->setDescription('Magento Store Online Order')
                ->setAmount($payment->getAmount(),2);

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

        $client->setUri(self::CGI_URL);
        $client->setConfig(array(
            'maxredirects'=>0,
            'timeout'=>30,
            //'ssltransport' => 'tcp',
        ));
        foreach ($request->getData() as $key => $value) {
            $request->setData($key, str_replace(self::RESPONSE_DELIM_CHAR, '', $value));
        }
//        $request->setXDelimChar(self::RESPONSE_DELIM_CHAR);

//        $client->setParameterPost($request->getData());
//        $client->setMethod(Zend_Http_Client::POST);

        $paramlist = Mage::helper('level3/paytrace')->_createParmList(
                array_keys($request->getData()), 
                $request->getData()
                );

        try {
            $responseBody = Mage::helper('level3/paytrace')->_call($paramlist);
        } catch (Exception $e) {
            $result->setResponseCode(-1)
                ->setResponseReasonCode($e->getCode())
                ->setResponseReasonText($e->getMessage());

            $debugData['result'] = $result->getData();
            $this->_debug($debugData);
            Mage::throwException($this->_wrapGatewayError($e->getMessage()));
        }

        $debugData['responseBody'] = $responseBody;
        
        if ($responseBody) {
            $result
                ->setResponseCode(self::RESPONSE_CODE_APPROVED)
//                ->setResponseSubcode((int)str_replace('"','',$responseBody[1]))
//                ->setResponseReasonCode((int)str_replace('"','',$responseBody[2]))
                ->setResponseReasonText($responseBody['RESPONSE'])
//                ->setApprovalCode($responseBody[4])
//                ->setAvsResultCode($responseBody[5])
                ->setTransactionId($responseBody['TRANSACTIONID'])
                ->setInvoiceNumber($request->getData('invoice'))
                ->setDescription($request->getData('description'))
                ->setAmount($request->getData('amount'))
                ->setMethod($request->getData('method'))
                ->setTransactionType($request->getData('tranxtype'))
                ->setCustomerId($request->getData('custid'))
//                ->setMd5Hash($responseBody[37])
//                ->setCardCodeResponseCode($responseBody[38])
//                ->setCAVVResponseCode( (isset($responseBody[39])) ? $responseBody[39] : null)
//                ->setSplitTenderId($responseBody[52])
//                ->setAccNumber($responseBody[50])
//                ->setCardType($responseBody[51])
//                ->setRequestedAmount($responseBody[53])
//                ->setBalanceOnCard($responseBody[54])
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

    /**
     * This function returns full transaction details for a specified transaction ID.
     *
     * @link http://www.authorize.net/support/ReportingGuide_XML.pdf
     * @link http://developer.authorize.net/api/transaction_details/
     * @param string $transactionId
     * @return Varien_Object
     */
    protected function _getTransactionDetails($transactionId)
    {
        $requestBody = sprintf(
            '<?xml version="1.0" encoding="utf-8"?>'
            . '<getTransactionDetailsRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">'
            . '<merchantAuthentication><name>%s</name><transactionKey>%s</transactionKey></merchantAuthentication>'
            . '<transId>%s</transId>'
            . '</getTransactionDetailsRequest>',
            $this->getConfigData('login'),
            $this->getConfigData('trans_key'),
            $transactionId
        );

        $client = new Varien_Http_Client();
        $client->setUri(self::CGI_URL_TD);
        $client->setConfig(array('timeout'=>45));
        $client->setHeaders(array('Content-Type: text/xml'));
        $client->setMethod(Zend_Http_Client::POST);
        $client->setRawData($requestBody);

        $debugData = array('request' => $requestBody);

        try {
            $responseBody = $client->request()->getBody();
            $debugData['result'] = $responseBody;
            $this->_debug($debugData);
            libxml_use_internal_errors(true);
            $responseXmlDocument = new Varien_Simplexml_Element($responseBody);
            libxml_use_internal_errors(false);
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('paygate')->__('Payment updating error.'));
        }

        $response = new Varien_Object;
        $response
            ->setResponseCode((string)$responseXmlDocument->transaction->responseCode)
            ->setResponseReasonCode((string)$responseXmlDocument->transaction->responseReasonCode)
            ->setTransactionStatus((string)$responseXmlDocument->transaction->transactionStatus)
        ;
        return $response;
    }

    /**
     * Validate payment method information object
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate()
    {
        return $this;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|Mage_Core_Model_Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'payment/revolution/'.$field;
        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Return authorize payment request
     *
     * @return Mage_Paygate_Model_Authorizenet_Request
     */
    protected function _getRequest()
    {
        $request = Mage::getModel('paygate/authorizenet_request')
            ->setTerms('Y')
            ->setUn($this->getConfigData('login'))
            ->setPswd($this->getConfigData('trans_key'));

        return $request;
    }
}