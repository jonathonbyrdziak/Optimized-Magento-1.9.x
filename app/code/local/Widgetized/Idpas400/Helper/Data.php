<?php
/**
 * 
 */
class Widgetized_Idpas400_Helper_Data extends Mage_Core_Helper_Abstract {
    
    const VERSION = 'v1.0.2';
    
    var $_types = array(
        'school'    => 'school',
        'business'  => 'business',
        'individual'=> 'individual',
        'nonprofit' => 'nonprofit',
        'government'=> 'government',
        'other'     => 'other'
    );
    
    /**
     * 
     * @return type
     */
    public function check_order_status() {
        $messages = array();
        foreach($this->getProcessingOrders() as $order) {
            $erpOrder = Mage::getModel('idpas400/erp_orders')->loadByTempId( $order->getId() );
            $messages[] = $this->_updateOrderStatusFromErp( $erpOrder );
        }
        return $messages;
    }
    
    /**
     * 
     * @return type
     */
    public function getProcessingOrders() {
        $collection = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_PROCESSING))
                ->load();
        
        return $collection;
    }
    
    /**
     * http://staging.b4schools.com/externaldb/ping?erporderid=
     * 
     * @param string $orderid
     */
    public function updateOrderStatusFromErp(string $orderid) {
        $erpOrder = Mage::getModel('idpas400/erp_orders')->load($orderid);
        return $this->_updateOrderStatusFromErp( $erpOrder );
    }
    
    /**
     * http://staging.b4schools.com/externaldb/ping?erporderid=
     * 
     * @param string $orderid
     */
    public function _updateOrderStatusFromErp(Widgetized_Idpas400_Model_Erp_Orders $erpOrder) {
        $success = array();
        
        // loading the ERP order
        if (!$erpOrder->getId()) return "No order in the ERP was found with id : $orderid";
        
        // loading by the incremental order id
        $iid = $erpOrder->getOrderNumber();
        $order = Mage::getModel('sales/order')->loadByIncrementId($iid);
        if (!$order->getId()) return "No customer facing (incremental) order id found : $iid";
        
        // set the tracking numbers
        if ($trackingNumbers = $erpOrder->getTrackingNumbers()) {
            if (!$order->canShip()) {
                return "The order is not ready to be shipped.";
                
            } else {
                foreach((array)$trackingNumbers as $shipmentTrackingNumber) {
                    try {
                        $shipment = Mage::getModel('sales/service_order', $order)
                                        ->prepareShipment($this->_getItemQtys($order));

                        $arrTracking = array(
                            'carrier_code' => $order->getShippingCarrier()->getCarrierCode(),
                            'title' => $order->getShippingCarrier()->getConfigData('title'),
                            'number' => $shipmentTrackingNumber,
                        );
                        
                        $track = Mage::getModel('sales/order_shipment_track')->addData($arrTracking);
                        $shipment->addTrack($track);
                        
                        // Register Shipment
                        $shipment->register();
                        
                        // Save the Shipment
                        $this->_saveShipment($shipment, $order);
                        
                        // Finally, Save the Order
                        $order->save();
                        $success[] = "Tracking number $shipmentTrackingNumber was added to the order";
                    } catch (Exception $e) {
                        return $e->getMessage();
                    }
                }
            }
        }
        
        // set the order's status
        if ($status = $erpOrder->getStatus()) {
            $order->setData('state', $status);
            $order->setData('status', $status);
            $success[] = "Order status was updated to : $status";
        }
        
        return implode(',',$success);
    }
    
    /**
     * 
     * @param Mage_Sales_Model_Order $order
     * @return type
     */
    public function getTrackingCodes(Mage_Sales_Model_Order $order) {
        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
                ->setOrderFilter($order)
                ->load();
        
        $tracknums = array();
        foreach ($shipmentCollection as $shipment) {
            foreach ($shipment->getAllTracks() as $tracknum) {
                $tracknums[] = $tracknum->getNumber();
            }
        }
        return $tracknums?:false;
    }
     
    /**
     * Get the Quantities shipped for the Order, based on an item-level
     * This method can also be modified, to have the Partial Shipment functionality in place
     *
     * @param $order Mage_Sales_Model_Order
     * @return array
     */
    protected function _getItemQtys(Mage_Sales_Model_Order $order) {
        $qty = array();

        foreach ($order->getAllItems() as $_eachItem) {
            if ($_eachItem->getParentItemId()) {
                $qty[$_eachItem->getParentItemId()] = $_eachItem->getQtyOrdered();
            } else {
                $qty[$_eachItem->getId()] = $_eachItem->getQtyOrdered();
            }
        }

        return $qty;
    }

    /**
     * Saves the Shipment changes in the Order
     *
     * @param $shipment Mage_Sales_Model_Order_Shipment
     * @param $order Mage_Sales_Model_Order
     * @param $customerEmailComments string
     */
    protected function _saveShipment(Mage_Sales_Model_Order_Shipment $shipment, Mage_Sales_Model_Order $order, $customerEmailComments = '') {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($order)
                ->save();

        $emailSentStatus = $shipment->getData('email_sent');
        if (!is_null($customerEmail) && !$emailSentStatus) {
            $shipment->sendEmail(true, $customerEmailComments);
            $shipment->setEmailSent(true);
        }

        return $this;
    }

    /**
     * Saves the Order, to complete the full life-cycle of the Order
     * Order status will now show as Complete
     *
     * @param $order Mage_Sales_Model_Order
     */
    protected function _saveOrder(Mage_Sales_Model_Order $order) {
        if ($status) {
        }
        $order->save();

        return $this;
    }
    
    /**
     *  Field Name FMT Start Lngth Dec Key Field Description     
        ZPFMZ3      A      1     3     K01 SHIP ZIP3 ORIGINATING 
        ZPTOZ3      A      4     3     K02 SHIP ZIP3 DESTINATION 
        ZPMOTC      A      7     3     K03 Manner of transport   
        ZPZONE      A     10     3         DESTINATION ZONE      
        ZPSROM      A     13     3         Warehouse number      
        ZPSTMP      A     16    26         LAST UPDATE   
     * 
        0 => 
          array (size=5)
            'ZPFMZ3' => string '913' (length=3)
            'ZPTOZ3' => string '980' (length=3)
            'ZPMOTC' => string 'UPG' (length=3)
            'ZPSROM' => string 'B4S' (length=3)
            'ZPZONE' => string '005' (length=3)
     * @param type $zip
     * @return int
     */
    public function getShippingDiscount( $zip, $carrier = 'UPG', $quote ) {
        
        $carrier = isset($this->carriers[$carrier])
                ? $this->carriers[$carrier]
                : $carrier;
        
//        -UPS Zip/Zone file 
        $zone = Mage::getSingleton('idpas400/db')
                ->fetch_row("SELECT ZPFMZ3,ZPTOZ3,ZPMOTC,ZPSROM,ZPZONE,ZPFRES"
                . " FROM Z1OB4ZIPZ"
                . " WHERE ZPMOTC = '$carrier' AND SUBSTRING('$zip',1,3)=ZPTOZ3");
        if (!$zone) return 0;
        extract($zone);
        
        // Flag to let tim set the order
        if ($ZPFRES == 'Y') {
            return true;
        }
        
        $amount = 0;
        $orderItems = $quote->getItemsCollection();
        foreach ($orderItems as $line) {
            $_product = Mage::getModel('catalog/product')->load($line->product_id);

//            -Product pricing / Cost / MSRP / Base shipping
            $sql = "SELECT * FROM Z1OB4SKUZ"
                . " WHERE SZPRDC='{$_product->getData('sku')}'"
                . " AND SZSROM='$ZPSROM'"
                . " AND SZMOTC='$ZPMOTC'";
            $record = Mage::getSingleton('idpas400/db')
                ->fetch_row($sql);

            $amount += ($line->getData('qty') * $record['SZSHCO']);
        }
        
        return $amount;
    }
    
    protected $carriers = array(
        'ups' => 'UPG'
    );
    
    /**
     * 
     * @param type $tax_category
     * @param type $tax_class
     * @return type
     */
    public function getTaxClassId($tax_category, $tax_class) {
        if (!$tax_class) return 0;
        
        // check it if exists
        $taxCategories = new Mage_Tax_Model_Class_Source_Product;
        $classes = $taxCategories->getAllOptions();
        
        $tax_class_id = 0;
        foreach ($classes as $class) {
            if (strpos($class['label'],$tax_class)!==false) {
                $tax_class_id = $class['value'];
                break;
            }
        }
        
        // create it if it doesn't exist
        if (!$tax_class_id) {
            $classModel = Mage::getModel('tax/class')->setData(array(
                'class_type' => Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT,
                'class_name' => "$tax_category - $tax_class",
                'op_avatax_code' => $tax_class
            ));
            $classModel->save();
            $tax_class_id = $classModel->getId();
        }
        
        return $tax_class_id;
    }
    
    /**
     * 
     * @param type $customer
     * @return type
     */
    public function isVerified( $customer ) {
        $data = Mage::getSingleton('idpas400/db')
                ->fetch_row("SELECT NANCA4 FROM SRONAM"
                . " WHERE NANUM='{$customer->getId()}'"
                . " AND NATYPP = '1'");
        if (!isset($data['NANCA4'])) return false;
        
        switch(strtoupper($data['NANCA4'])) {
            case 'VERIFI':      return true;    break;
            default:                            break;
        }
        return false;
    }
    
    /**
     * 
     * @param type $code
     * @param type $value
     * @param type $model
     * @return type
     */
    public function getAttributeOptionText( $value, $attributeCode = 'select_customer_type', $entityType = 'customer' ) {
        
        $entityType = Mage::getModel('eav/config')->getEntityType($entityType);
        $entityTypeId = $entityType->getEntityTypeId();

        $attribute = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setCodeFilter($attributeCode)
                        ->setEntityTypeFilter($entityTypeId)
                        ->getFirstItem();   
        
        $dataModel = Mage_Eav_Model_Attribute_Data::factory($attribute,$entityType);
        $dataModel->compactValue($value);
        
        $text = strtolower($dataModel->outputValue());
        if ($text=='government institution') $text = 'government';
        if ($text=='non-profit') $text = 'nonprofit';
        
        return $text;
    }
    
    /**
     * 
     * @param type $attributes
     * @return type
     */
    public function filterAttributesArray( $attributes, $customerTypeText ) {
        $types = $this->_types;
        // disable unrequired fields for this customer type
        unset($types[$customerTypeText]);
        
        $attributes = $this->_disableAttributesOfType($attributes, $types);
        
        return $attributes;
    }
    
    /**
     * 
     * @param type $attributes
     * @param type $customerTypeText
     */
    public function filterAttributesCollection( $attributes, $customerTypeText ){ 
        // disable unrequired fields for this customer type
        $types = $this->_types;
        
        // none of our custom attributes are required in the admin area
        foreach ($attributes as $attribute) {
            $attribute->setData('is_required',0);
        }
        
        // hide un-used attributes
        unset($types[$customerTypeText]);
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            foreach($types as $type) {
                $_code = str_replace('-','',$code);
                $_code = substr($_code,0,strlen($type));
                if ($_code != $type) continue;

                $attribute->setData('is_visible',0);
            }
        }
        return $attributes;
    }
    
    /**
     * 
     * @param type $attributes
     * @param type $type
     * @return type
     */
    protected function _disableAttributesOfType( $attributes, $types ) {
        foreach ($attributes as $code => $a) {
            // hide the non-customer type fields
            foreach($types as $type) {
                $_code = str_replace('-','',$code);
                $_code = substr($_code,0,strlen($type));
                if ($_code != $type) continue;

                unset($attributes[$code]);
            }
        }
        return $attributes;
    }
    
    /**
     * 
     * @param type $quote
     */
    public function apply_coupon( $quote, $request ) {
        $couponCode = $request->getParam('payment_coupon_code');
        if (!$couponCode && !$request->getParam('coupon_ajax', false)) return;
        
        $result = false;
        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            if ($codeLength) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->setCouponCode($couponCode)
                    ->collectTotals()
                    ->save();

                if ($isCodeLengthValid && $couponCode == $quote->getCouponCode()) {
                    $result = $this->__('<span style="color:green;"><strong>Coupon code "%s" was applied.</strong></span>', Mage::helper('core')->escapeHtml($couponCode));
                } else {
                    $result = $this->__('<span style="color:red;">Coupon code "%s" is not valid.</span>', Mage::helper('core')->escapeHtml($couponCode));
                }
            } else {
                $result = $this->__('<span style="color:red;">Coupon code was canceled.</span>');
            }
        } catch (Exception $ex) {
           $result = $this->__('<span style="color:red;">Cannot apply the coupon code.</span>');
        }

        // if this is ajax, then return and quit
        if ($request->getParam('coupon_ajax', false)) {
            echo $result;
            die;
        }
    }
    
    /**
     * 
     * @param type $entity_id
     * @param type $qty
     */
    function update_product_quantities($entity_id, $qty, $debug) {
        $coreResource = Mage::getSingleton('core/resource');
        $write = $coreResource->getConnection('core_write');
        
        $result = $write->query("UPDATE cataloginventory_stock_item s_i, cataloginventory_stock_status s_s
                    SET     s_i.qty = '$qty', s_i.is_in_stock = IF('$qty'>0, 1,0),
                    s_s.qty = '$qty', s_s.stock_status = IF('$qty'>0, 1,0)
              WHERE s_i.product_id = '$entity_id' AND s_i.product_id = s_s.product_id ");
    }

    /**
     * 
     * @param type $sku
     * @return type
     */
    function getProductBySku($sku) {
        $coreResource = Mage::getSingleton('core/resource');
        $write = $coreResource->getConnection('core_write');
        
        $entity_row = $write->query("SELECT entity_id "
            . "FROM catalog_product_entity p_e WHERE p_e.sku = '$sku'")
                ->fetchObject();
        
        $entity_id = $entity_row->entity_id;
        return $entity_id;
    }
    
    /**
     * 
     * @param type $msg
     * @param type $score
     * @param type $debug
     * @return boolean
     */
    public function tr( $msg, $score, $debug ) {
        if (!$debug) return false;
        if ($msg===false) {
            echo "<tr><td colspan='2'>";
        } else {
            echo "<tr><td>$msg</td><td>";
        }
        
        if ($score===true) echo '<span style="color:green">Passed</span>';
        elseif ($score===false) echo '<span style="color:red">Failed</span>';
        elseif (is_array($score) || is_object($score)) {
            echo '<pre>';
            print_r($score);
            echo '</pre>';
        }
        else echo $score;
        
        echo '</td></tr>';
    }

    /**
     * 
     * @param type $customer
     */
    public function syncCustomer( $customer = false, $order = false, $debug = false ) {
        if ($debug) echo '<h4>Inserting a customer</h4><table border="1">';
        $type = Mage::helper('idpas400')->getAttributeOptionText(
                $customer->getData('select_customer_type')
            );
        $category_1 = $type=='school' ? 'SCHOOL' : 'CONSUM';
        
        $this->tr('Customer Type', $type, $debug);
        
        // set school type
        $category_2 = 'NA';
        $category_3 = 'NA';
        if ($category_1 == 'SCHOOL') {
//            CHARTE  Charter school 
//            NA      Not applicable 
//            PRIVAT  Private school 
//            PUBLIC  Public school
            switch((int)$customer->getData('school_type')) {
                case 94:
                    $category_2 = "PUBLIC";
                    break;
                case 92:
                    $category_2 = "PRIVAT";
                    break;
                case 91:
                    $category_2 = "CHARTE";
                    break;
            }
//            COMBIN  Combined      
//            NA      Not applicable
//            PRIMAR  Primary       
//            SECOND  Secondary

//            <select class="jcf-hidden" id="school_grade_level" name="school_grade_level">
//            <option value="" selected="selected"></option>
//            <option value="4"> Licensed Daycare – Pre-Kindergarten</option>
//            <option value="3">Kindergarten - Elementary School</option>
//            <option value="88">Middle School</option>
//            <option value="87">High School</option>
//            <option value="86">Career, Technical, Trade or Vocational School</option>
//            <option value="85">College or University</option>
//            <option value="84">Other</option>
//            </select>
            switch((int)$customer->getData('school_grade_level')) {
                case 0:
                    $category_3 = 'PRIMAR';
                    break;
                case 1:
                    $category_3 = 'SECOND';
                    break;
            }
        }
//        ACTIVE  Active                      
//        PENDIN  Pending email verification  
//        VERIFI  Active Verified School   
        $category_4 = 'PENDIN';
        
//        -Create a new customer in enterprise
//        Enterprise maintains the basic customer information for logistics purposes, The web application will maintain all the customer name / address information for the customer ordering and any demographic reporting, order reporting, invoice reporting, etc. The actual shipping delivery name and address will be provided in the order header at the time an order is entered and uploaded to Enterprise.  
//        The file is Z1OB4SCUS, in library ID2662AFB4. 
//        This file contains the data required to set up a customer in Enterprise. 
//        The add or change for enterprise is performed by Enterprise programs when a record is inserted into this file. 
//         File Name........ Z1OB4SCUS                                                     
//         Library........   ID2662AFLV                                                 
//         Format Descr.....                                                              
//         Format Name...... Z1B4SCUS                                                      
//         File Type........ PF            Unique Keys - N                                
//         Field Name FMT Start Lngth Dec Key Field Description     
        $data = array(
            'N1NUM'   => $customer->getId(),//     A      1    11     K01 Customer/Supplier number                    
            'N1IDOC'  => 'B4B2C',//     A     12    10         Internal document name                      
            'N1NAME'  => '',//     A     22    50         Name                                        
            'N1ADR1'  => '',//     A     72    50         Address line 1                              
            'N1ADR2'  => '',//     A    122    50         Address line 2                              
            'N1ADR3'  => '',//     A    172    50         Address line 3                              
            'N1ADR4'  => '',//     A    222    50         Address line 4                              
            'N1POCD' => '',//      A    272    16         Postal code                                 
            'N1SPCD' => '',//      A    288     2         State/Province Code                         
            'N1CNTY' => '',//      A    290     5         County code                                 
            'N1PHNO' => $customer->getData('telephone'),//      A    295    35         Phone number                                
            'N1CTFX'  => '',//     A    330    35         Fax number                                  
            'N1LANG'  => 'EN',//     A    365     3         Language                                    
//            'N1TREG'  => '',//     A    368    16         VAT registration number                     
            'N1CONT' => substr($customer->getData('firstname').' '.$customer->getData('lastname'),0,50),//      A    384    20         Contact person                              
//            'N1CARD' => '',//      A    404    10         Credit card                                 
//            'N1CCEX' => '',//      S    414     6  00     Credit card expiration date (YYYYMM)        
//            'N1CRCN' => '',//      A    420    25         Credit card number                          
//            'N1CAHO'  => '',//     A    445    30         Card holder                                 
//            'N1ADNONC' => '',//    S    475     3  00     Address number                              
//            'N1COUNNC' => '',//    A    478     4         Country                                     
//            'N1SPCDNC' => '',//    A    482     2         State/Province Code                         
//            'N1CNTYNC' => '',//    A    484     5         County code                                 
//            'N1POCDNC' => '',//    A    489    16         Postal code                                 
//            'N1ADR1NC' => '',//    A    505    35         Address line 1                              
//            'N1ADR2NC' => '',//    A    540    35         Address line 2                              
//            'N1ADR3NC' => '',//    A    575    35         Address line 3                              
//            'N1ADR4NC' => '',//    A    610    35         Address line 4                              
            'N1STAT' => '1',//      A    645     1         Status                                      
            'N1MAIL' => $customer->getData('email'),//      A    646    50         E-mail address                              
            'N1NCA1' => $category_1,//      A    696     6         Business partner category 1                 
            'N1NCA2' => $category_2,//      A    702     6         Business partner category 2                 
            'N1NCA3' => $category_3,//      A    708     6         Business partner category 3                 
            'N1NCA4' => $category_4,//      A    714     6         Business partner category 4                 
//            'N1NCA5' => '',//      A    720     6         Business partner category 5                 
//            'N1NCA6' => '',//      A    726     6         Business partner category 6                 
//            'N1RETC' => '',//      A    732     2         Return code                                  
            'N1STMP1' => '',//     A    734    26                                                     
          );
        
        $customerAddressId = $customer->getDefaultBilling();
        if ($customerAddressId) {
             $billing = Mage::getModel('customer/address')->load($customerAddressId);
             $data['N1ADR1'] = substr($billing->getData('street'),0,50);
             $data['N1ADR4'] = substr($billing->getData('city'),0,50);
             $data['N1SPCD'] = strlen($billing->getData('region'))==2
                     ? $billing->getData('region')
                     : $this->convert_state($billing->getData('region'));
             $data['N1POCD'] = $billing->getData('postcode');
             $data['N1CNTY'] = 'US';
        }
        if ($order) {
             $billing = $order->getBillingAddress();
             if ($billing->getData('street')) {
                $data['N1ADR1'] = substr($billing->getData('street'),0,50);
                $data['N1ADR4'] = substr($billing->getData('city'),0,50);
                $data['N1SPCD'] = strlen($billing->getData('region'))==2
                     ? $billing->getData('region')
                     : $this->convert_state($billing->getData('region'));
                $data['N1POCD'] = $billing->getData('postcode');
                $data['N1CNTY'] = 'US';
             }
        }
        
        switch($type) {
            case 'individual':
                $data['N1NAME'] = $data['N1CONT'];
                break;
            case 'nonprofit':
                $data['N1NAME'] = substr($customer->getData('nonprofit_name'),0,50);
                break;
            case 'government institution':
            case 'governmentinstitution':
            case 'government-institution':
            case 'government':
                $data['N1NAME'] = substr($customer->getData('government_agency'),0,50);
                break;
            case 'business':
                $data['N1NAME'] = substr($customer->getData('business_name'),0,50);
                break;
            case 'school':
                $data['N1NAME'] = substr($customer->getData('school_name'),0,50);
                // School address
                $data['N1ADR1'] = substr(ucwords(strtolower(
                                    $customer->getData('school_address1')
                                    )),0,50);
                $data['N1ADR2'] = substr(ucwords(strtolower(
                                    $customer->getData('school_address2')
                                    )),0,50);
                
                // city
                $data['N1ADR4'] = substr(ucwords(strtolower(
                                    $customer->getData('school_city')
                                    )),0,50);
                
                // state
                $data['N1SPCD'] = $this->convert_state(ucwords(strtolower(
                                    Mage::helper('idpas400')->getAttributeOptionText(
                                    $customer->getData('school_state'),
                                    'school_state')
                                    )));
                
                // postalcode
                $data['N1POCD'] = $customer->getData('school_postal_code');
                $data['N1CNTY'] = 'US';
                
                break;
        }
        
        
        $this->tr('structured insert data', $data, $debug);
        Mage::getModel('idpas400/db')->insert_row($data, 'Z1OB4SCUS', $debug);
        if ($debug) echo '</table>';
    }
    
    /**
     * 
     * @param type $order
     * @param type $origData
     */
    public function syncOrder( $order = false, $debug = false ) {
        // get order item collection
        $orderItems = $order->getItemsCollection();
        
        if ($debug) echo '<h4>Inserting '. count($orderItems).' order lines</h4>';
        
        $ii=0;
        foreach ($orderItems as $i => $line) {
            
            if ($debug)echo "<strong>Order Line $i</strong><table border='1' style='background-color:#eee'>";
            $_product = Mage::getModel('catalog/product')->load($line->product_id);
//            $_categories = $_product->getCategoryIds();
//            var_dump($line->getData());var_dump($_product->getData());
            
//            File Name........ Z1OB4SSOL                                                    
//            Library........   ID2662AFLV                                                 
//            Format Descr.....                                                              
//            Format Name...... Z1B4SSOL                                                     
//            File Type........ PF            Unique Keys - N                                
//            Field Name         FMT      Start       Lngth         Dec       Key      Field        Description
//            var_dump($line->getData());die;
            $data = array(
                'OLTORN' => $order->getId(),//      S      1    12  00 K01 Temporary order number assigned by WebSite                 
                'OLLIN6' => $ii++,//      S     13     6  00 K02 Order line number 6.0                       
                'OLSROM' => 'B4S',//      A     19     3         Warehouse number “B4S” or determined whse from zone table                           
                'OLPRDC' => $_product->getData('sku'),//      A     22    35         Item - SKU#                                      
                'OLUNIT' => 'CS',//      A     57     5         Unit                                        
                'OLQT15' => round($line->getData('qty_ordered'),0),//      S     62    15  03     Order quantity 15.3 Quantity ordered                         
                'OLSALP' => round($line->getData('base_price'),2),//      S     77    17  04     Sales price -  Total item price – less tax                                 
                'OLFOCC' => 'N',//      A     94     1         Free of charge Y/N                          
                'OLDELT' => '0',//      S     95     8  00     Dispatch time                               
                'OLRDDT' => '0',//      S    103     8  00     Requested dispatch time                     
//                'OLDESC' => addslashes(substr(strip_tags($_product->getData('description')),0,50)),//      A    111    50         Item description                            
//                'OLPCOD' => '',//      A    161     3         Item price code                             
                'OLTDCD' => 'N',//      A    164     1         Transit delivery Y/N                        
                'OLDDCD' => 'N',//      A    165     1         Direct delivery Y/N                         
                'OLFICC' => 'N',//      A    166     1         Fictitious item Y/N                         
                'OLBALC' => 'N',//      A    167     1         Backlog Y/N                                 
//                'OLSHPM' => '',//      A    168    30         Shipment marking                            
                'OLCOSP' => '0',//      S    198    17  04     Cost price                                  
                'OLDSPC' => 'N',//      A    215     1         Dispatch repricing Y/N                      
//                'OLOVDC' => '',//      A    216     1         Valid for order discount Y/N                
                'OLCONO' => '0',//      S    217     8  00     Contract number                             
                'OLSTRU' => '0',//      S    225     1  00     Order structure code                        
//                'OLSUNO' => '',//      A    226    11         Supplier number                             
                'OLGWGT' => round($line->getData('weight'),0),//      S    237    11  05     Gross weight - sum of weights                                
                'OLNWGT' => '0',//      S    248    11  05     Net weight                                  
                'OLGVOL' => '0',//      S    259    11  05     Gross volume                                
                'OLNVOL' => '0',//      S    270    11  05     Net volume                                  
//                'OLTECN' => '',//      A    281    10         Engineer                                    
//                'OLNOTR' => '',//      A    291     3         Nature of transaction                       
//                'OLPOVA' => '',//      A    294     4         Port of arrival/dispatch                    
//                'OLOCOU' => '',//      A    298     4         Country of origin                           
//                'OLCSNO' => '',//      A    302    18         Commodity code                              
//                'OLSHPG' => '',//      A    320     5         Shipment group                              
                'OLCONV' => '0',//      S    325    15  09     Quantity conversion factor                  
//                'OLPOTP' => '',//      A    340     2         Purchase order type                         
                'OLTXKY' => '0',//      S    342    15  00     Text key                                    
                'OLWTKY' => '0',//      S    357    15  00     Text key - Work field                       
//                'OLERCD' => '',//      A    372     1         Error code                                  
//                'OLPRIL' => '',//      A    373     5         Price list                                  
//                'OLPLTY' => '',//      A    378     3         Price type                                  
                'OLFOCO' => '0',//      S    381     1  00     Free of charge item line code               
                'OLGDSQ' => '0',//      S    382    15  00     General discount key                        
                'OLTAM1' => round($line->getData('tax_amount'),2),//      S    397    17  04     Tax amount 1 -  Total line tax                               
            );
            $this->tr('structured insert data', $data, $debug);
            Mage::getModel('idpas400/db')->insert_row($data, 'Z1OB4SSOL', $debug);
            if ($debug) echo '</table>';
        }
        
//        File Name........ Z1OB4SSOH                                                   
//        Library........   ID2662AFB4                                                   
//        Format Descr.....                                                              
//        Format Name...... Z1B4SSOH                                                     
//        File Type........ PF            Unique Keys - N                                
//          Field Name            FMT Start Lngth Dec Key Field Description  
        if ($debug) echo '<h4>Inserting order header</h4><table border="1" style="background-color:#e1e1e1">';
        $data = array(
            'OHTORN' => $order->getId(),//      S      1    12  00 K01 Temporary order number assigned by WebSite
            'OHCRED' => date('Ymd',strtotime($order->getData('created_at'))),//      S     13     8  00     Creation date YYYYMMDD      
            'OHCRTM' => date('His',strtotime($order->getData('created_at'))),//      S     21     6  00     Creation time HHMMSS        
            'OHUSER' => 'B4SUSER',//      A     27    10         User id                                     
            'OHCUNO' => $order->getData('customer_id'),//      A     37    11         Customer number – Assigned by WebSite                        
            'OHORDT' => 'B4',//      A     48     2         Order type                                  
            'OHSALE' => 'ASA',//      A     50    10         Salesman                                    
            'OHHAND' => 'ASA',//      A     60    10         Handler                                     
            'OHSROM' => 'B4S',//      A     70     3         Warehouse number                            
            'OHPCUR' => $order->getData('order_currency_code'),//      A     73     4         Primary currency                            
//            'OHORNO' => '',//      S     77    12  00     Order number - unassigned
            'OHNAME' => $order->getData('customer_firstname').' '.$order->getData('customer_lastname'),//      A     89    30         Name – Customer name           
            'OHODAT' => date('Ymd',strtotime($order->getData('created_at'))),//      S    119     8  00     Order date YYYYMMDD                                 
            'OHDANO' => '00',//      S    127     3  00     Dispatch address number
            'OHCANO' => '00',//      S    130     3  00     Confirmation address number                 
            'OHICNO' => $order->getData('customer_id'),//      A    133    11         Invoice customer number                     
            'OHCOPE' => $order->hasData('po_number')//      A    144    20         Customer reference – Level 3 PO number
                            ? $order->getData('po_number')
                            : is_object($order->getQuote())
                                ? $order->getQuote()->getData('po_number')
                                : 0,
            'OHINCO' => '00',//      S    164     2  00     Number of invoice copies                    
            'OHLANG' => 'EN',//      A    166     3         Language                                    
            'OHTAXC' => 'N',//      A    169     1         VAT Y/N                                     
            'OHBALC' => 'N',//      A    170     1         Backlog Y/N                                 
//            'OHDISG' => '',//      A    171     2         Discount group                              
//            'OHPRIL' => '',//      A    173     5         Price list                                  
//            'OHBTBL' => '',//      A    178     5         BTB price list default                      
//            'OHSNOL' => '',//      A    183     5         Serial Number Controlled price list default                          
            'OHPINC' => 'N',//      A    188     1         Periodic invoicing Y/N                      
            'OHMINC' => 'N',//      A    189     1         Merge invoicing Y/N                         
//            'OHSTXC' => '',//      A    190     3         Standard text number                        
            'OHHORD' => 'N',//      A    193     1         Hold order Y/N                              
//            'OHTOPC' => '',//      A    194     3         Terms of payment 
            'OHCDAY' => '000',//      S    197     3  00     Credit days                                 
            'OHTODC' => 'NA',//      A    200     3         Terms of delivery                           
            'OHMOTC' => 'UPG',//      A    203     3         Manner of transport                         
            'OHFREF' => $order->getData('shipping_amount') 
                + $order->getData('shipping_tax_amount'),//      S    206    12  03     Freight fee - Sum of UPG over base price                                  
            'OHPOSF' => '0',//      S    218    12  03     Postage fee                                 
            'OHINSF' => '0',//      S    230    12  03     Insurance fee                               
            'OHADMF' => '0',//      S    242    12  03     Administration fee                          
            'OHINVF' => '0',//      S    254    12  03     Invoice fee                                 
            'OHOREF' => $order->getIncrementId(),//      A    266    35         Customers/Suppliers order number reference – Website order ref#
//            'OHGDSM' => '',//      A    301    30         Goods marking                               
            'OHHINV' => 'N',//      A    331     1         Hold invoice                                
//            'OHDADE' => '',//      A    332    10         Dispatch address engineer                   
//            'OHDADL' => '',//      A    342    10         Dispatch address location                   
//            'OHDCOU' => '',//      A    352     4         Country dispatched to/ arrived from         
//            'OHDTRG' => '',//      A    356    16         VAT registration number of debtor address   
//            'OHNOTR' => '',//      A    372     3         Nature of transaction                       
//            'OHVAHC' => '',//      A    375     4         VAT handling code                           
//            'OHPOVA' => '',//      A    379     4         Port of arrival/dispatch                    
//            'OHTCOU' => '',//      A    383     4         Country of trader                           
            'OHIORD' => 'N',//      A    387     1         Internal order                              
//            'OHTSRM' => '',//      A    388     3         To warehouse                                
//            'OHROUT' => '',//      A    391    10         Route ID                                    
//            'OHDEPA' => '',//      A    401    10         Departure ID                                
//            'OHDEST' => '',//      A    411    10         Destination ID                              
//            'OHSHPA' => '',//      A    421    11         Shipping agent                              
//            'OHDENO' => '',//      A    432    11         Debtor number                               
            'OHEANO' => '000',//      S    443     3  00     Debtor address number                       
            'OHIANO' => '000',//      S    446     3  00     Invoice address number                      
            'OHWOCD' => 'Y',//      A    449     1         Web order Y/N                               
            'OHCONF' => 'Y',//      A    450     1         Confirmed Y/N                               
            'OHCIDE' => $order->getData('remote_ip'),//      A    451    30         Client identity - web customer #                           
//            'OHTXKY' => '0',//      S    481    15  00     Text key                                    
//            'OHWTKY' => '0',//      S    496    15  00     Text key - Work field          
//            Shipping Address
            'O1NAME' => $order->getShippingAddress()->getData('firstname').' '.
                $order->getShippingAddress()->getData('lastname'),//      A    511    30         Disp.addr.name - ship to name                            
            'O1ADR1' => $order->getShippingAddress()->getData('street'),//      A    541    35         Disp.addr 1 - ship to address                                
            'O1ADR2' => $order->getShippingAddress()->getData('company'),//      A    576    35         Disp.addr 2                                 
            'O1ADR3' => 'Telephone: '.$order->getShippingAddress()->getData('telephone'),//      A    611    35         Disp.addr 3                                 
            'O1ADR4' => $order->getShippingAddress()->getData('city'),//      A    646    35         Disp.addr 4                                 
            'O1POCD' => $order->getShippingAddress()->getData('postcode'),//      A    681    16         Disp.addr post code                         
            'O1CNTY' => '',//      A    697     5         Disp.addr county                            
            'O1SPCD' => $this->convert_state($order->getShippingAddress()->getData('region')),//      A    702     2         Disp.addr state                             
            'O1COUN' => $order->getShippingAddress()->getData('country_id'),//      A    704     4         Disp.addr country                           
            'O1MAIL' => $order->getShippingAddress()->getData('email'),//      A    708    50         Disp.e-mail address 
//            Confirmation Address (not used)
            'O2NAME' => '',//      A    758    30         Conf.addr.name                              
            'O2ADR1' => '',//      A    788    35         Conf.addr 1                                 
            'O2ADR2' => '',//      A    823    35         Conf.addr 2                                 
            'O2ADR3' => '',//      A    858    35         Conf.addr 3                                 
            'O2ADR4' => '',//      A    893    35         Conf.addr 4                                 
            'O2POCD' => '',//      A    928    16         Conf.addr post code                         
            'O2CNTY' => '',//      A    944     5         Conf.addr county                            
            'O2SPCD' => '',//      A    949     2         Conf.addr state                             
            'O2COUN' => '',//      A    951     4         Conf.addr country                           
            'O2MAIL' => '',//      A    955    50         Conf.e-mail address      
//            Invoice Address
            'O3NAME' => '',//      A   1005    30         Invo.addr.name                              
            'O3ADR1' => '',//      A   1035    35         Invo.addr 1                                 
            'O3ADR2' => '',//      A   1070    35         Invo.addr 2                                 
            'O3ADR3' => '',//      A   1105    35         Invo.addr 3                                 
            'O3ADR4' => '',//      A   1140    35         Invo.addr 4                                 
            'O3POCD' => '',//      A   1175    16         Invo.addr post code                         
            'O3CNTY' => '',//      A   1191     5         Invo.addr county                            
            'O3SPCD' => '',//      A   1196     2         Invo.addr state                             
            'O3COUN' => '',//      A   1198     4         Invo.addr country                           
            'O3MAIL' => '',//      A   1202    50         Invo.e-mail address                         
            'O4NAME' => '',//      A   1252    30         Debt.addr.name                              
            'O4ADR1' => '',//      A   1282    35         Debt.addr 1                                 
            'O4ADR2' => '',//      A   1317    35         Debt.addr 2                                 
            'O4ADR3' => '',//      A   1352    35         Debt.addr 3                                 
            'O4ADR4' => '',//      A   1387    35         Debt.addr 4                                 
            'O4POCD' => '',//      A   1422    16         Debt.addr post code                         
            'O4CNTY' => '',//      A   1438     5         Debt.addr county                            
            'O4SPCD' => '',//      A   1443     2         Debt.addr state                             
            'O4COUN' => '',//      A   1445     4         Debt.addr country   
            
            'OHERCD' => '',//      A   1449     1         Error code                                  
            'OHMODU' => 'NETSTORE',//      A   1450    10         Module name                                 
            'OHAUTF' => 'N',//      A   1460     1         Auto fulfilment                             
            'OHOFRU' => '1',//      A   1461     1         Order line fulfilment rule                  
            'OHCMPD' => 'N',//      A   1462     1         Complete delivery                           
            'OHMAIL' => $order->getData('customer_email'),//      A   1463    50         Valid E-mail address                              
            'OHPCDT' => date('Ymd',strtotime($order->getData('created_at'))),//      S   1513     8  00     Pricing date YYYYMMDD                               
//            'OHSAME' => '',//      A   1521     5         Sales promotion                             
//            'OHSFA1' => '',//      S   1526    17  04     SETUP FEE TAXED                             
            'OHTAM1' => $order->getData('tax_amount'),//      S   1543    17  04     Tax amount 1 – total tax calculated  
        );
        $this->tr('structured insert data', $data, $debug);
        Mage::getModel('idpas400/db')->insert_row($data, 'Z1OB4SSOH', $debug);
        if ($debug) echo '</table>';
    }
    
    /**
     * 
     * @param type $name
     * @param type $to
     * @return type
     */
    function convert_state($name, $to = 'abbrev') {
        $states = array(
            array('name' => 'Alabama', 'abbrev' => 'AL'),
            array('name' => 'Alaska', 'abbrev' => 'AK'),
            array('name' => 'Arizona', 'abbrev' => 'AZ'),
            array('name' => 'Arkansas', 'abbrev' => 'AR'),
            array('name' => 'California', 'abbrev' => 'CA'),
            array('name' => 'Colorado', 'abbrev' => 'CO'),
            array('name' => 'Connecticut', 'abbrev' => 'CT'),
            array('name' => 'Delaware', 'abbrev' => 'DE'),
            array('name' => 'Florida', 'abbrev' => 'FL'),
            array('name' => 'Georgia', 'abbrev' => 'GA'),
            array('name' => 'Hawaii', 'abbrev' => 'HI'),
            array('name' => 'Idaho', 'abbrev' => 'ID'),
            array('name' => 'Illinois', 'abbrev' => 'IL'),
            array('name' => 'Indiana', 'abbrev' => 'IN'),
            array('name' => 'Iowa', 'abbrev' => 'IA'),
            array('name' => 'Kansas', 'abbrev' => 'KS'),
            array('name' => 'Kentucky', 'abbrev' => 'KY'),
            array('name' => 'Louisiana', 'abbrev' => 'LA'),
            array('name' => 'Maine', 'abbrev' => 'ME'),
            array('name' => 'Maryland', 'abbrev' => 'MD'),
            array('name' => 'Massachusetts', 'abbrev' => 'MA'),
            array('name' => 'Michigan', 'abbrev' => 'MI'),
            array('name' => 'Minnesota', 'abbrev' => 'MN'),
            array('name' => 'Mississippi', 'abbrev' => 'MS'),
            array('name' => 'Missouri', 'abbrev' => 'MO'),
            array('name' => 'Montana', 'abbrev' => 'MT'),
            array('name' => 'Nebraska', 'abbrev' => 'NE'),
            array('name' => 'Nevada', 'abbrev' => 'NV'),
            array('name' => 'New Hampshire', 'abbrev' => 'NH'),
            array('name' => 'New Jersey', 'abbrev' => 'NJ'),
            array('name' => 'New Mexico', 'abbrev' => 'NM'),
            array('name' => 'New York', 'abbrev' => 'NY'),
            array('name' => 'North Carolina', 'abbrev' => 'NC'),
            array('name' => 'North Dakota', 'abbrev' => 'ND'),
            array('name' => 'Ohio', 'abbrev' => 'OH'),
            array('name' => 'Oklahoma', 'abbrev' => 'OK'),
            array('name' => 'Oregon', 'abbrev' => 'OR'),
            array('name' => 'Pennsylvania', 'abbrev' => 'PA'),
            array('name' => 'Rhode Island', 'abbrev' => 'RI'),
            array('name' => 'South Carolina', 'abbrev' => 'SC'),
            array('name' => 'South Dakota', 'abbrev' => 'SD'),
            array('name' => 'Tennessee', 'abbrev' => 'TN'),
            array('name' => 'Texas', 'abbrev' => 'TX'),
            array('name' => 'Utah', 'abbrev' => 'UT'),
            array('name' => 'Vermont', 'abbrev' => 'VT'),
            array('name' => 'Virginia', 'abbrev' => 'VA'),
            array('name' => 'Washington', 'abbrev' => 'WA'),
            array('name' => 'West Virginia', 'abbrev' => 'WV'),
            array('name' => 'Wisconsin', 'abbrev' => 'WI'),
            array('name' => 'Wyoming', 'abbrev' => 'WY')
        );

        $return = false;
        foreach ($states as $state) {
            if ($to == 'name') {
                if (strtolower($state['abbrev']) == strtolower($name)) {
                    $return = $state['name'];
                    break;
                }
            } else if ($to == 'abbrev') {
                if (strtolower($state['name']) == strtolower($name)) {
                    $return = strtoupper($state['abbrev']);
                    break;
                }
            }
        }
        return $return;
    }
}
