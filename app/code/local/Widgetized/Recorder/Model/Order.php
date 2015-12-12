<?php
/**
 * Description of Order
 * 
 * @var id
 * @var parent_id
 * @var increment_id
 * @var subtotal
 * @var shipping_amount
 * @var tax_amount
 * @var grand_total
 * @var skus
 * @var billing address
 * @var shipping address
 * @var customer_id
 * @var interval
 * @var recurring_start_date
 * @var failed_attempt
 * @var reminder_sent
 * 
 * @var created_at
 * @var updated_at
 * 
 * @author Jonathon
 */
class Widgetized_Recorder_Model_Order extends Mage_Core_Model_Abstract {
    /**
     * 
     */
    public function _construct() {
        parent::_construct();
        $this->_init('recorder/order');
    }

    /**
     * Load object data
     *
     * @param   integer $id
     * @return  Mage_Core_Model_Abstract
     */
    public function load($id, $field=null)
    {
        $this->setData(array());
        $this->_quote = null;
        
        parent::load($id, $field);
        return $this;
    }
    
    /**
     * 
     */
    public function reset() {
        $this->getQuote( true );
        $this->setData('enabled', 1);
        $this->setData('failed_attempt', 0);
        $this->setData('reminder_sent', 0);
        $this->setData('errors', 0);
        $this->updateTotals();
    }
    
    /**
     * 
     * @param type $order_id
     */
    public function bind( $order_id ) {
        $order = Mage::getModel('sales/order')->load($order_id);
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $customer = Mage::getModel('customer/customer')->load($customerId);
        
        $this->setData('enabled', 0);
        $this->setData('store_id', $order->getStore()->getId());
        $this->setData('interval', '1 month');
        $this->setData('failed_attempt', 0);
        $this->setData('reminder_sent', 0);
        $this->setData('created_at', date(Widgetized_Recorder_Helper_Data::DATE_FORMAT, time()));
        $this->setData('updated_on', date(Widgetized_Recorder_Helper_Data::DATE_FORMAT, time()));
        $this->setData('start_date', date(Widgetized_Recorder_Helper_Data::DATE_FORMAT, time()));
        
        $this->setData('customer_id', $customerId);
        $this->setData('shipping_address', $order->getShippingAddress()->getId());
        $this->setData('billing_address', $order->getBillingAddress()->getId());
        
        $this->setData('subtotal', $order->getData('subtotal'));
        $this->setData('shipping_amount', $order->getData('base_shipping_amount'));
        $this->setData('tax_amount', $order->getData('tax_amount'));
        $this->setData('grand_total', $order->getData('grand_total'));
        
        $skus = array();
        foreach ($order->getAllItems() as $item) {
            $skus[$item->getSku()] = $item->getQtyOrdered();
        }
        $this->setData('skus', serialize($skus));
        
        $this->save();
        
        $order->setData('parent_id', $this->getId());
        $order->save();
    }
    
    /**
     * This method creates the quote if it does not exist in the database
     * associated with this recurring order.
     * 
     * @return type
     */
    public function _createQuote( $force = false ) {
        $this->_quote = Mage::getModel('sales/quote');
        $quoteId = $this->getData('quote_id');
        
        if (!$force && $quoteId) {
//            $this->_quote->load( $quoteId );
        }
        
        if ($this->getId() != $this->_quote->getData('parent_id')) {
            $this->_quote = Mage::getModel('sales/quote');
        }
        
        if (!$this->_quote->getId()) {
            $customerId = $this->getCustomerId();
            $customer = Mage::getModel('customer/customer')->load($customerId);

            /* -------------------- Create Default Quote Object -----------------*/
            $this->_quote->setStore($this->_quote->getStore()->load( $customer->getStoreId() ));
            $this->_quote->setIsMultiShipping( false );
            $this->_quote->setIsActive(false);
            $this->_quote->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER);
            $this->_quote->save();

            /* -------------------- Update Quote With Order -----------------*/
            $this->_quote->setData('parent_id', $this->getId());
            $this->_quote->assignCustomer($customer);
            $this->_quote->setData('customer_id', $customerId);

            $address = $this->getBillingAddressObj();
            $this->_quote->setBillingAddress($address);

            /* -------------------- Add Order Products To Quote -----------------*/
            $weight = 0;
            $_products = array();
            foreach($this->_quote->getAllVisibleItems() as $_item) {
                $_products[] = $_item->getSku();
            }
            foreach ($this->getSkus() as $sku => $qty) {
                if (!in_array($sku,$_products)) {
                    $this->_addProduct($sku, $qty);
                }
            }

            /* -------------------- Start Processing Totals -----------------*/
            $this->_quote->save();
            
            $address = $this->getShippingAddressObj();
            $this->_quote->setShippingAddress($address);
            $this->_quote->getShippingAddress()->setSameAsBilling(0);
            $this->_quote->getShippingAddress()->setShippingMethod(Widgetized_Recorder_Helper_Data::shippingMethod);
            $this->_quote->getShippingAddress()->save();
            
            $this->_quote->save();
            $this->_quote->getShippingAddress()->implodeStreetAddress();
            $this->_quote->getShippingAddress()->validate();
            
            $payment = $this->_quote->getPayment(); // Mage_Sales_Model_Quote_Payment
            $payment->setMethod(Widgetized_Recorder_Helper_Data::PAYMENTMETHOD);
            $this->_quote->setPayment($payment);
            
            $this->setData('quote_id', $this->_quote->getId());
            $this->_quote->setRecurring($this);
            $this->save(); // save the recurring order with its quote association
            
            $this->updateTotals();
        }
        
        // update the product qty if it's not correct
        if ($this->_quote->getItemsQty() != $this->getQty()) {
            $this->updateProducts($this->getSkus());
        }
        
        return $this->_quote;
    }
    
    /**
     * 
     */
    public function updateTotals( $force = false ) {
        if (!$this->_quote) {
            $this->_quote = $this->getQuote();
        }
        
        if (!$this->getData('totals') || $force) {
            $this->_quote->getBillingAddress();
            $this->_quote->getShippingAddress()->setCollectShippingRates(true);
            $this->_quote->setTotalsCollectedFlag(false);
            $this->_quote->save();

            try {
                $this->_quote->collectTotals();
            } catch(Exception $e) {
                $message = 'Attempting reminder email on recurring order #'
                        .$this->getId().': '.$e->getMessage()."\r\n";
                $this->addError($message);
            }

            $block = Mage::app()->getLayout()->createBlock(
                'recorder/account_totals',
                'totals',
                array('template' => 'recurring/order/totals.phtml')
            );

            $block->setRecurring($this);

            $this->setData('totals', str_replace('Merchandise Subtotal','Grand Total', $block->toHtml() ));
            $this->setData('subtotal', $this->_quote->getData('subtotal'));
            $this->setData('shipping_amount', $this->_quote->getData('shipping'));
            $this->setData('tax_amount', $this->_quote->getTaxAmount());
            $this->setData('grand_total', $this->_quote->getGrandTotal());
            $this->setData('shipping_description', $this->_quote->getData('shipping_description'));

//            Mage::log('Recurring order '.$this->getId()
//                    .' -discount: '.Mage::getModel('customer/session')->getData('shippingDiscount')
//                    .' -shipping rate: '.Mage::getModel('customer/session')->getData('shippingRate')
//                    .' -shipping amount: '.$this->_quote->getData('shipping'),
//                    null,'updateTotals.log');

            $this->save();
        }
        return $this;
    }
    
    /**
     * 
     */
    public function placeOrder() {
        Mage::unregister('recurring_order');
        Mage::register('recurring_order', $this);

        Mage::log("Placing order : ".$this->getId(),null,'place_recurring_orders.log');
        if (!$this->_quote) {
            $this->_quote = $this->getQuote();
        }
        
        $this->_quote->getShippingAddress()->setResidentialIndicator( $this->getShippingIndicator() );
        $this->updateTotals( true );
        
        $payment = $this->_quote->getPayment(); // Mage_Sales_Model_Quote_Payment
        $payment->setMethod(Widgetized_Recorder_Helper_Data::PAYMENTMETHOD);
        $this->_quote->setPayment($payment);
        
        $quote = $this->_quote;
        $quote->setRecurring($this);
        
        try {
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();
            
            $order = $service->getOrder();
            if (!$order) {
                throw new exception('$service->getOrder() failed to return an order');
            }
            
            $order->setRecurring($this);
            Mage::dispatchEvent('checkout_type_onepage_save_order_after',
                array('order'=>$order, 'quote'=>$quote));

            /**
             * we only want to send to customer about new order when there is no redirect to third party
             */
            if ($order->getCanSendNewEmailFlag()) {
                $order->sendNewOrderEmail();
            }

            // Finalize the CHILD ORDER
            $order->setCreatedAt(date('Y-m-d H:m:s', time()));
            $order->setData('parent_id', $this->getId());
            $order->save();
            
        } catch (Exception $e){
            $this->setData('failed_attempt', $this->getData('failed_attempt')+1);
            $message = 'Attempt '.$this->getData('failed_attempt').' on recurring order #'.$this->getId().': '.$e->getMessage()."\r\n";
            $this->addError($message);
            return $message;
        }
        
        // update the order
        $this->setData('reminder_sent', 0);
        $this->setData('failed_attempt', 0);
        $this->setData('errors', 0);
        $this->setData('updated_on', date(Widgetized_Recorder_Helper_Data::DATE_FORMAT, time()));
        
        $this->setData('subtotal', $order->getData('subtotal'));
        $this->setData('shipping_amount', $order->getData('base_shipping_amount'));
        $this->setData('tax_amount', $order->getData('tax_amount'));
        $this->setData('grand_total', $order->getData('grand_total'));
        
        // it gets saved here
        return $this->updateStartDate();
    }
    
    /**
     * 
     * @return type
     */
    public function canProcess() {
        if ($this->getId()=='38') return true;
//        if ($this->getErrorMessage()) return false;
//        if ($this->has_errors()) return false;
        if (!Mage::helper('recorder')->dateHasPassedOrIsToday($this->getData('start_date'))) return false;
        return true;
    }
    
    /**
     * 
     */
    public function canSendReminder() {
        if ($this->getData('reminder_sent', 0)) return false;
        return Mage::helper('recorder')->dateIsDaysBefore($this->getData('start_date'));
    }
    
    public function isEnabled() {
        return $this->getData('enabled');
    }
    
    /**
     * 
     * @return type
     */
    public function getQuote( $force = false ) {
        // get and set the quote
        if ($force || is_null($this->_quote)) {
            $this->_quote = $this->_createQuote( $force );
        }
        return $this->_quote;
    }
    
    /**
     * Method locates the shipping indicator any place that it can and then
     * remembers it
     * 
     * @return type
     */
    public function getShippingIndicator() {
        $indicator = parent::getData('shipping_indicator');
        if (!$indicator) {
            $indicator = $this->getQuote()->getShippingAddress()->getResidentialIndicator();
            if (!$indicator) {
                Mage::dispatchEvent('widgetized_validate_address', array(
                    $this->_eventObject => $this,
                    'order' => $this->getQuote()
                ));
                $indicator = $this->getQuote()->getShippingAddress()->getResidentialIndicator();
            }
            $this->setData('shipping_indicator',$indicator);
            $this->save();
        }
        return $indicator;
    }
    
    /**
     * 
     * @return type
     */
    public function getShippingDescription() {
        if (!$this->getData('shipping_description')) {
            $indicator = $this->getShippingIndicator();
            switch ($indicator) {
                case 1:
                case '1':
                    $type = 'Residential';
                    break;
                case 2:
                case '2':
                    $type = 'Commercial';
                    break;
                default:
                    $type = 'Unknown';
                    break;
            }
            $this->setData('shipping_description', $type);
        }
        return $this->getData('shipping_description');
    }
    
    /**
     * 
     * @param type $interval
     */
    public function getInterval() {
        $intervals = array(
            '3 month' => 'Every Quarter',
            '1 month' => 'Every 30 Days',
            '2 week'  => 'Every 2 Weeks',
            '1 week'  => 'Every Week',
            '1 day'   => 'Every Day',
            '5 minutes'   => 'Every 5 Minutes'
        );
        return $intervals[$this->getData('interval')];
    }
    
    /**
     * We can't allow the billing address to be anything but the default
     * billing address, until we can save multiple credit cards.
     * 
     */
    public function getBillingAddress() {
        return $this->getCustomer()->getDefaultBilling();
    }
    
    /**
     * 
     */
    public function getBillingAddressObj() {
//        This order_address is only for the order associated with the order
        $addr = Mage::getModel('customer/address')->load( $this->getBillingAddress() );
        
        $quoteAddress = Mage::getModel('sales/quote_address');
        $quoteAddress->importCustomerAddress($addr);
        
        return $quoteAddress;
    }
    
    /**
     * 
     */
    public function getShippingAddressObj() {
        $addr = Mage::getModel('sales/order_address')->load( $this->getShippingAddress() );
        
        $quoteAddress = Mage::getModel('sales/quote_address');
//        $quoteAddress->importOrderAddress($addr);
        $addrData = $addr->getData();
        $quoteAddress->setData($addrData);
        
        return $quoteAddress;
    }
    
    /**
     * 
     */
    public function getSkus(){
        return unserialize(parent::getData('skus'));
    }
    public function setSkus($skus) {
        $this->setData('skus', serialize($skus));
    }
    public function deleteSku( $sku ) {
        $skus = $this->getSkus();
        unset($skus[$sku]);
        $this->setSkus($skus);
        return count($this->getSkus());
    }
    public function updateSku($sku, $qty) {
        $skus = $this->getSkus();
        $skus[$sku] = $qty;
        $this->setSkus($skus);
    }
    public function addSku($sku, $qty) {
        $skus = $this->getSkus();
        if (isset($skus[$sku])) {
            $skus[$sku] = $skus[$sku] + $qty;
        } else {
            $skus[$sku] = $qty;
        }
        $this->setSkus($skus);
    }
    
    /**
     * 
     * @return type
     */
    public function getCreditcard() {
        return Mage::helper('level3')->getPrimaryCard();
    }
    
    /**
     * 
     */
    public function getCustomer() {
        if (is_null($this->_customer) || !$this->_customer->getId()) {
            $customer = Mage::getModel('customer/customer');
            $customer->load( $this->getCustomerId() );
            $this->_customer = $customer;
        }
        return $this->_customer;
    }
    
    /**
     * 
     */
    public function getCustomerName() {
        return $this->getCustomer()->getFirstname().' '.$this->getCustomer()->getLastname();
    }
    
    public function getItemCount() {
        $count = 0;
        foreach ($this->getSkus() as $sku => $qty) {
            $count += $qty;
        }
        return $count;
    }

    /**
     * Get product object based on requested product information
     *
     * @param   mixed $productInfo
     * @return  Mage_Catalog_Model_Product
     */
    protected function _getProduct($productInfo)
    {
        $product = null;
        if ($productInfo instanceof Mage_Catalog_Model_Product) {
            $product = $productInfo;
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productInfo);
        } 
        if (!$product->getId()) {
            $productId = Mage::helper('idpas400')->getProductBySku($productInfo);
            $product = Mage::getModel('catalog/product')->load($productId);
        }
        $currentWebsiteId = Mage::app()->getStore()->getWebsiteId();
        if (!$product
            || !$product->getId()
            || !is_array($product->getWebsiteIds())
            || !in_array($currentWebsiteId, $product->getWebsiteIds())
        ) {
            Mage::throwException(Mage::helper('checkout')->__('The product could not be found.'));
        }
        return $product;
    }

    /**
     * Get request for product add to cart procedure
     *
     * @param   mixed $requestInfo
     * @return  Varien_Object
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof Varien_Object) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new Varien_Object(array('qty' => $requestInfo));
        } else {
            $request = new Varien_Object($requestInfo);
        }

        if (!$request->hasQty()) {
            $request->setQty(1);
        }

        return $request;
    }
    
    /**
     * Add product to the recurring order (quote)
     *
     * @param   int|Mage_Catalog_Model_Product $productInfo
     * @param   mixed $requestInfo
     * @return  Widgetized_Recorder_Model_Order
     */
    public function addProduct($productInfo, $requestInfo = null) {
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);
        
        if (!$this->_addProduct($product, $request)) return false;
        $this->addSku($product->getSku(), $request->getQty());
    }
    
    /**
     * Add product to the recurring order (quote)
     *
     * @param   int|Mage_Catalog_Model_Product $productInfo
     * @param   mixed $requestInfo
     * @return  Widgetized_Recorder_Model_Order
     */
    public function _addProduct($productInfo, $requestInfo=null) {
        if (!$this->_quote) {
            $this->_quote = $this->getQuote();
        }
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);

        $productId = $product->getId();

        if ($product->getStockItem()) {
            $minimumQty = $product->getStockItem()->getMinSaleQty();
            //If product was not found in cart and there is set minimal qty for it
            if ($minimumQty && $minimumQty > 0 && $request->getQty() < $minimumQty
                && !$this->_quote->hasProductId($productId)
            ){
                $request->setQty($minimumQty);
            }
        }
        
        if ($productId) {
            try {
                $result = $this->_quote->addProduct($product, $request);
                
            } catch (Mage_Core_Exception $e) {
                $message = $e->getMessage();
                Mage::getSingleton('core/session')->addError($message);
//                $this->addError($message);
                return false;
            }
        } else {
            $message = 'Recurring order #'.$this->getId().': The product does not exist.'."\r\n";
            $this->addError($message);
            return false;
        }

        Mage::dispatchEvent('checkout_cart_product_add_after', 
                array('quote_item' => $result, 'product' => $product));
        return true;
    }

    /**
     * Update cart items information
     *
     * @param   array $data
     * @return  Mage_Checkout_Model_Cart
     */
    public function updateProducts($data)
    {
        $this->setSkus($data);
        if (!$this->_quote) {
            $this->_quote = $this->getQuote();
        }
        
        $this->_updateProducts($data);
        return $this;
    }

    /**
     * Update cart items information
     *
     * @param   array $data
     * @return  Mage_Checkout_Model_Cart
     */
    public function _updateProducts($data)
    {
        if (!$this->_quote) {
            $this->_quote = $this->getQuote();
        }
        
        Mage::dispatchEvent('checkout_cart_update_items_before', 
                array('cart'=>$this, 'info'=>$data));

        foreach ($data as $sku => $qty) {
            $item = $this->hasSku($sku);
            if (!$item) {
                continue;
            }

            if (!$qty) {
                $this->removeProduct($sku);
                $this->deleteSku($item->getSku());
                continue;
            }

            if ($qty > 0) {
                $item->setQty($qty);

                $itemInQuote = $this->_quote->getItemById($item->getId());
                
                if (!$itemInQuote && $item->getHasError()) {
                    $message = 'Recurring order #'.$this->getId().': '.$item->getMessage()."\r\n";
                    $this->addError($message);
                }
//                $itemInQuote->save();
            }
        }
        $this->save();
        
        Mage::dispatchEvent('checkout_cart_update_items_after', 
                array('cart'=>$this, 'info'=>$data));
        return $this;
    }

    /**
     * Remove item from cart
     *
     * @param   int $sku
     * @return  Mage_Checkout_Model_Cart
     */
    public function removeProduct($sku)
    {
        if (!$this->_quote) {
            $this->_quote = $this->getQuote();
        }
        $item = $this->hasSku($sku);
        $this->_quote->removeItem($item->getId());
        return $this;
    }

    /**
     * Checking product exist in Quote
     *
     * @param int $sku
     * @return bool
     */
    public function hasSku($sku)
    {
        foreach ($this->getAllItems() as $item) {
            if ($item->getSku() == $sku) {
                return $item;
            }
        }
        return false;
    }
    
    public function getAllItems() {
        if (!$this->_quote) {
            $this->_quote = $this->getQuote();
        }
        return $this->_quote->getAllItems();
    }
    
    /**
     * Returns the quote totals, creating them if the do not exist
     * 
     * @return type
     */
    public function getTotals() {
        if (!$this->getData('totals') 
                || empty($this->getData('totals'))
                || $this->getGrandTotal() != $this->_quote->getGrandTotal()) {
           $this->updateTotals();
        }
        
        return $this->getData('totals');
    }
    
    /**
     * 
     */
    public function has_errors() {
        if (!$this->_quote) {
            $this->_quote = $this->getQuote();
        }
        if (!$this->isEnabled()) {
            return 'This order is not enabled';
        }
        if (!$this->getData('enabled')) {
            return 'This recurring order is not enabled';
        }
        if ($this->getData('failed_attempt')>2) {
            $errors = $this->getErrors();
            if ($errors && strlen($errors)>2) {
                return $errors;
            } else {
                return 'This recurring order has too many failed attempts';
            }
        }
        if (!$this->getData('customer_id')) {
            return 'This recurring order is not associated with a customer';
        }
        if (!$this->getData('shipping_address')) {
            return 'This recurring order does not have a shipping address';
        }
        if (!$this->getData('billing_address')) {
            return 'This recurring order does not have a billing address';
        }
        if (empty($this->getSkus())) {
            return 'This recurring order does not have any products';
        }
        $errors = $this->getCreditcard()->getToken();
        if ($errors && strlen($errors)>2) {
            return $errors;
        }
        if (!Mage::helper('level3')->getPrimaryCard()) {
            return 'You must have a valid credit card on file for recurring orders to be placed';
        }
        $errors = $this->_quote->getHasError();
        if ($errors && strlen($errors)>2) {
            if (is_array($errors)) $errors = implode(', ',$errors);
            return $errors;
        }
        try {
            $addressValidation = $this->_quote->getShippingAddress()->validate();
        } catch (Exception $e) {
            return 'Shipping address validate() :'.$e->getMessage();
        }
//        if ($addressValidation !== true) {
//            return Mage::helper('sales')->__('Please check shipping address information. %s', implode(' ', $addressValidation));
//        }
        $method = $this->_quote->getShippingAddress()->getShippingMethod();
        if (!$method) {
            return 'Please specify a shipping method.';
        }
        
        $rate  = $this->_quote->getShippingAddress()->getShippingRateByCode($method);
//        if (!$rate && !$this->_quote->getShippingAddress()->getData('shipping_amount')) {
//            return 'No shiping rates could be found.';
//        }
        try {
            $addressValidation = $this->_quote->getBillingAddress()->validate();
        } catch (Exception $e) {
            return 'Billing address validate() :'.$e->getMessage();
        }
        if ($addressValidation !== true) {
            return Mage::helper('sales')->__('Please check billing address information. %s', implode(' ', $addressValidation));
        }

        if (!($this->_quote->getPayment()->getMethod())) {
            return Mage::helper('sales')->__('Please select a valid payment method.');
        }
        return false;
    }
    
    /**
     * 
     * @param type $method
     */
    public function setShippingMethod( $method = false ) {
        if (!$this->_quote) {
            $this->_quote = $this->getQuote();
        }
        $method = $method ? $method : Widgetized_Recorder_Helper_Data::shippingMethod;
        $this->_quote->getShippingAddress()->setShippingMethod($method);
        $this->_quote->save();
        return $this;
    }
    
    /**
     * Don't ever forget to save the quote when we're saving the recurring order
     * 
     */
    public function save() {
        if (!$this->_quote) {
            $this->_quote = $this->getQuote();
        }
        $this->_quote->save();
        $this->setData('updated_on', date(Widgetized_Recorder_Helper_Data::DATE_FORMAT, time()));
        
        $results = Mage::helper('recorder')->dateHasPassed($this->getData('start_date'));
        if ($results) {
            $this->setData('start_date',  date(Widgetized_Recorder_Helper_Data::DATE_FORMAT, time()));
        }
        
        return parent::save();
    }
    
    /**
     * 
     */
    public function nextOrderDate() {
        $date = date_parse(date(Widgetized_Recorder_Helper_Data::DATE_FORMAT, time()));
        list($i, $period) = explode(' ', $this->getData('interval'));

        switch($period) {
            case 'yr':
            case 'year':
            case 'years':
                $date['year'] = $date['year'] + $i;
                break;
            case 'month':
            case 'monthes':
            case 'months':
            case 'mo':
                $date['month'] = $date['month'] + $i;
                break;
            case 'wk':
            case 'week':
            case 'weeks':
                $date['day'] = $date['day'] + ($i * 7);
                break;
            case 'day':
            case 'days':
                $date['day'] = $date['day'] + $i;
                break;
            case 'm':
            case 'min':
            case 'minute':
            case 'minutes':
                $date['minute'] = $date['minute'] + $i;
                break;
        }
        $next_time = mktime($date['hour'],$date['minute'],0,$date['month'],$date['day'],$date['year']);
        return date(Widgetized_Recorder_Helper_Data::DATE_FORMAT, $next_time);
    }
    
    /**
     * 
     */
    public function updateStartDate() {
        $this->setData('start_date', $this->nextOrderDate());
        $this->save();
        return true;
    }
    
    public function getQty() {
        $qty = 0;
        foreach($this->getSkus() as $sku => $_qty) $qty += $_qty;
        return $qty;
    }
    
    /**
     * This method is only used for notifications, it should not be used
     * to determine the eligability of placing an order.
     * 
     * @return type
     */
    public function getErrorMessage() {
        if ($msg = $this->getErrors()) {
            return $msg;
        }
        if ($msg = $this->has_errors()) {
            return $msg;
        }
        return false;
    }
    public function getStatusMessage() {
        if ($this->canProcess()) {
            return "Your order #".$this->getId()." has passed all checks and is ready to be placed tonight!";
        }
        
        if ($this->getData('reminder_sent')) {
            return "We have emailed you a reminder for this order.";
        } elseif ($this->canSendReminder()) {
            return "Tonight we'll be sending you a reminder email about this recurring order.";
        }
        return false;
    }
    
    /**
     * 
     * @param type $msg
     */
    public function addError( $msg ) {
        $errors = $this->_getErrors();
        $errors[] = $msg;
        
        $this->setData('errors', json_encode($errors));
        $this->save();
        
        // notify the man in charge
        mail('jonathonbyrd@gmail.com', 'Error on Recurring order #'.$this->getId(), $msg);
    }
    
    public function getErrors() {
        $errors = $this->_getErrors();
        if (empty($errors)) return '';
        
        if (is_array($errors)) {
            $errors = implode("\r\n".'<br/>', $errors);
        }
        return $errors;
    }
    
    public function _getErrors() {
        $errors = $this->getData('errors');
        if (!$errors) {
            $errors = array();
        } elseif (is_string($errors)) {
            if ($msg = json_decode($errors, true)) {
                $errors = $msg;
            } else {
                $errors = array();
            }
        }
        if (!is_array($errors)) $errors = array();
        return $errors;
    }
}
