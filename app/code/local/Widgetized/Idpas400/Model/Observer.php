<?php

/**
 * 
1.0 Magento > Customer
When a new individual/school registers with Magento, Magento will connect to 
 * the ERP to save the users data to the ERP.

When an individual/school updates their account.

   1.1 Magento will search the ERP for the users email address. If the user
 *  exists, this user record will be updated.

   1.2 If the user is not found in the ERP database, Magento will create a new
 *  user.


2.0 Magento > Customer Address
When an individual/school adds a new address, Magento will connect to the ERP
 *  database to add the new address.

When an individual/school updates an existing address.

   2.1 Magento will search the ERP for the user, to obtain the users ID. 
 * Magento will search the ERP for the address based off of the numbers in the 
 * address. If the numbers match, then the address matches. (Tim, maybe you
 *  might have a better address matching algorithm)

   2.2 Magento updates or adds the address as necessary


3.0 Magento > New Order
When a new order is placed in Magento, Magento will connect to the ERP system 
 * and create the order within the ERP.

   3.1 The ERP order ID will be saved against the ORDER within Magento.
   3.2 The ERP Invoice ID will be saved against the INVOICE within Magento.
   3.3 Payments made on the Magento Invoice will be created as payments within 
 * the ERP. and the payment ID will be saved against the PAYMENT within Magento.

   3.4 When an order, invoice, or payment is updated, such will be found in the
 *  ERP and update the ERP record.


4.0 Products
A CRON (timed event) will run ever hour. This CRON will pull all products 
 * within the ERP, that are marked for this ecommerce store. Magento will 
 * match these products against the Magento products using the SKU code.

   4.1 If the product does or does not exist, inventory, prices, images,
 *  categories, and meta data will be updated. The ERP data will always be
 *  used as the master data and always overwrite all Magento data.


5.0 Categories
A CRON will run every day. This CRON will pull all categories from the ERP and
 *  create or update them within Magento. The category SLUG will be used to
 *  match categories

 */
class Widgetized_Idpas400_Model_Observer {
    
    /**
     * 
     */
    public function cron_check_order_status() {
        $messages = Mage::helper('idpas400')->check_order_status();
        echo json_encode($messages);
        die;
    }
    
    /**
     * B4Requirement
     * Updating the shipping rates with the discount provided by the AS400
     * 
     * There is a special hook that we've had to hack the core and add, in order
     * for this to work. Without the special hook described below we wont be able
     * to adjust the rates before they are used by the rest of the system.
     * 
     * Specially injected code into file
     * C:\wamp\www\b4schools\app\code\core\Mage\Checkout\controllers\OnepageController.php
     * on line 298, just before the $session gets cleared() in the successAction method
     * 
     * // INJECTED FOR B4SCHOOLS
     * Mage::dispatchEvent('checkout_onepage_controller_success_before_action', array('order_ids' => array($lastOrderId))); 
     * // --- END INJECTION
     * 
     * We need to create our own hook event because the session gets cleared and 
     * we need catch the order_id before it's cleared and lost.
     * 
     * @param Varien_Event_Observer $observer
     */
    public function update_shipping_rates(Varien_Event_Observer $observer) {
        $address = $observer->getQuoteAddress();
        $rates = $observer->getShippingRates();
        
        try {
            foreach ($rates as $rate) {
                $price = $rate->getCost();
                if (!$price) {
                    $price = $rate->getPrice();
                }
                $rate->setCost($price);
                $rate->setPrice($price);
                
                if ($rate->getCarrier()!='ups') continue;
                if ($rate->getMethod()!='03') continue;
                    
                // get the discount from the ERP
                $discount = Mage::helper('idpas400')->getShippingDiscount(
                        $address->getData('postcode'),
                        'UPG',
                        $address->getQuote());
                
                Mage::getModel('customer/session')->setData('shippingDiscount',$discount);
                Mage::getModel('customer/session')->setData('shippingRate',$price);
                
                if ($discount===true) {
                    $shippingPrice = 0;
                } else {
                    // adjusting shipping rate
                    $shippingPrice = 0;
                    $shippingPrice = $shippingPrice + $price;
                    $shippingPrice = $shippingPrice - $discount;
                    if ($shippingPrice < 0) $shippingPrice = 0;
                }

                $rate->setPrice( $shippingPrice );
                $rate->setCost( $shippingPrice );
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
    
    /**
     * 
     * @param Varien_Event_Observer $observer
     */
    public function checkout_onepage_controller_success_action(Varien_Event_Observer $observer) {
        $order_id = $observer->getEvent()->getOrderIds();
        
        // saving the order to the register so that we can use the info block
        Mage::register('current_order', Mage::getModel('sales/order')->load($order_id));
    }
    
    /**
     * 
     * @param Varien_Event_Observer $observer
     */
    public function customer_save_after(Varien_Event_Observer $observer) {
        
//        // @todo b4requirements Update the customer if it exists in the ERP
//        $customer = $observer->getEvent()->getCustomer();
//        Mage::helper('idpas400')->syncCustomer( $customer );
    }

    /**
     * 
     * @param Varien_Event_Observer $observer
     */
    public function customer_address_save_after(Varien_Event_Observer $observer) {
        
        // @todo b4requirements Update the customer if it exists in the ERP
//        $customerAddress = $observer->getEvent()->getCustomerAddress();
//        Mage::helper('idpas400')->syncCustomer( $customer );
    }

    /**
     * Magento passes a Varien_Event_Observer object as
     * the first parameter of dispatched events.
     * 
     * @param Varien_Event_Observer $observer
     */
    public function sales_order_save_commit_after(Varien_Event_Observer $observer) {
        $order = $observer->getEvent()->getOrder();
        $origData = $order->getOrigData();
        
        // do some debugging
        $debug = false;
        $msg = " ---------- Observer Started ----------  Order ID # ".$order->getId();
        if ($debug) echo $msg; else Mage::log($msg, null, 'erp.log');
        
        // we only want to run this once per session
        static $once;
        if (!isset($once) || is_null($once)) {
            $once = array();
        }
        if (in_array($order->getId(), $once)) return;
        
        // determine if the order is new
        $orderIsNew = ($order->getStatus() == Mage_Sales_Model_Order::STATE_PROCESSING 
                      && ($debug || $origData == null));
        
        // sync the order with the ERP
        if ($orderIsNew) {
            $once[] = $order->getId();
            
            if (!$debug) Mage::log("  ATTEMPT SYNC  ",null,'erp.log');
            Mage::helper('idpas400')->syncCustomer( $order->getCustomer(), $order, $debug );
            Mage::helper('idpas400')->syncOrder( $order, $debug );
            
        }
    }
    
    /**
     * 
     */
    public function update_product_quantities() {
        $debug = true;
        $erpProductCollection = Mage::getModel('idpas400/erp_products')
                ->getCollection();
        
        foreach ($erpProductCollection as $erp_product) {
            $entity_id = Mage::helper('idpas400')
                    ->getProductBySku($erp_product->getData('sku'));
        
            Mage::helper('idpas400')->update_product_quantities(
                    $entity_id, $erp_product->getData('qty'),$debug);
        }
        
    }
    
    /**
     * 
     * @param Varien_Event_Observer $observer
     */
    public function product_cron_sync() {
        ini_set("display_errors", 1);
        ini_set("memory_limit","1024M");
        
        $debug = true;
        $log = 'product_sync_'.date('d-m-Y').'.log';
        $path = dirname(dirname(Mage::getRoot())).DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
        
        $currentStore = Mage::app()->getStore()->getId();
        $storeId = Mage::app()->getStore()->getStoreId();
        $storeId = 1;
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        
        $rootCategoryId = Mage::app()->getStore($storeId)->getRootCategoryId();
        $parentCategory = Mage::getModel('catalog/category')->load($rootCategoryId);
        
        $erpProductCollection = Mage::getModel('idpas400/erp_products')
                ->getCollection();
        
        // Logging that this process is starting
//        if ($debug) {
//            Mage::log('------------------------------', null, $log);
//            Mage::log('Starting Cron Foreach of '.count($erpProductCollection)
//                    .' products', null, $log);
//        }
        
        foreach ($erpProductCollection as $erp_product) {
            $_product = Mage::getModel('catalog/product');
            $id = Mage::helper('idpas400')->getProductBySku($erp_product->getData('sku'));
            $_product->load($id);
            
            // logging to check that the product has loaded
//            if ($debug) 
//            Mage::log('- Product sku ('.$erp_product->getData('sku').') load ('.
//            $_product->getId().')',null,$log);
            
            // initial product set
            foreach ((array)$erp_product->getData() as $property => $value) {
                $_product->setData( $property, $value );
            }
            
            // new product setup
            if (!$_product->getId()) {
                $_product->setTypeId('simple');
                $_product->setData('attribute_set_id', 4);
                $_product->setWebsiteIds(array(1));
                // 1 =&gt; Not Visible Individually, 2 =&gt; Catalog, 3 =&gt; Search, 4 =&gt; Catalog, Search
                $_product->setVisibility(4);
                $_product->setData('page_layout', 'two_columns_left');
                $_product->setData('is_salable', 1);
                
                $_product->setData('stock_data',  array(
                    'manage_stock' => 1,
                    'use_config_manage_stock' => 0,
                    'qty' => $erp_product->getData('qty'),
                    'min_qty' => 0,
                    'use_config_min_qty' => 0,
                    'min_sale_qty' => 1,
                    'use_config_min_sale_qty' => 1,
                    'max_sale_qty' => 9999,
                    'use_config_max_sale_qty' => 1,
                    'is_qty_decimal' => 0,
                    'backorders' => 0,
                    'notify_stock_qty' => 0,
                    'is_in_stock' => ($erp_product->getData('qty')?1:0)
                ));
            }
            
            //COST
            if (!$_product->hasData('cost'))
                $_product->setData('cost', 0);
            if ($erp_product->hasData('cost'))
                $_product->setData('cost', $erp_product->getData('cost'));
            
            //PRICE
            if (!$_product->hasData('price'))
                $_product->setData('price', 0);
            if ($erp_product->hasData('price'))
                $_product->setData('price', $erp_product->getData('price'));
            
            //1=Enabled; 2=Disabled;
            $_product->setData('status', $erp_product->getData('status') ?2 :1);
            
            // Logging the status of this current record
//            if ($debug) 
//                Mage::log('- status ('. $_product->getData('status').')',null,$log);
            
            // map taxes
//            $_product->setData('tax_class_id', $erp_product->getData('tax_yn')=='Y' ?2 :0);
            if ( $erp_product->getData('tax_yn')=='Y' )
            {
                $taxClassId = Mage::helper('idpas400')->getTaxClassId(
                        'Avatax', $erp_product->getData('tax_category'));
                $_product->setData('tax_class_id', $taxClassId);
            } else {
                $_product->setData('tax_class_id', 0);
            }
            
            $images = array();
            try {
                // use the directory path to images you want to save for the product
                foreach((array)$erp_product->getData('images') as $filename) {
                    $filepath_to_image = str_replace('/b4school/skuimage/', $path, $filename);
                    if (!file_exists($filepath_to_image)) continue;
                    $images[] = $filepath_to_image;
                }
                
                // loggin product images found
//                if ($debug) 
//                    Mage::log('- images found ('.count($images).')',null,$log);
            
            } catch(Exception $e) {
                // logging product image failed
//                Mage::log('Image loading failure '.$e->getMessage(),null,$log);
                Mage::log('Image loading failure '.$e->getMessage(),null,'image_failed.log');
            }
                
            try {
                if ($images) {
                    if ($_product->getId()) {
                        // delete the existing images
                        $mediaApi = Mage::getModel("catalog/product_attribute_media_api");
                        $items = $mediaApi->items($_product->getId());
                        $attributes = $_product->getTypeInstance()->getSetAttributes();
                        $gallery = $attributes['media_gallery'];
        
                        // Other images found
//                        if ($debug) 
//                            Mage::log('- images to remove ('.count($items).')',null,$log);
                        
                        foreach($items as $item) {
                            if ($gallery->getBackend()->getImage($_product, $item['file'])) {
                                $gallery->getBackend()->removeImage($_product, $item['file']);
                            }
//                            $mediaApi->remove($_product->getId(), $item['file']);
                        }
                        $_product->setStoreId($currentStore)->save();
                    }
                }
            } catch(Exception $e) {
                // logging product image failed
                Mage::log('Image remove failure '.$e->getMessage(),null,$log);
            }

            try {
                if ($images) {
                    // actually save the images
                    $mode = array("small_image","thumbnail","image");
                    foreach ((array)$images as $image) {
                        
                        // Other images found
//                        if ($debug) 
//                            Mage::log('- saving image ('.$image.')',null,$log);
                        
                        $_product->addImageToMediaGallery($image, $mode, true, false);
                    }
                }
            } catch(Exception $e) {
                // logging product image failed
                Mage::log('Image save failure '.$e->getMessage(),null,$log);
            }
            
            //create/associate categories
            $catids = array();
            for ($i=2; $i<=6; $i++) {
                
                $cat_slug = $_product->getData("category_$i");
                if ($cat_slug) {
                    
                    $category = Mage::getModel('catalog/category')
                        ->getCollection()
                        ->addAttributeToFilter('url_key', strtolower($cat_slug))
                        ->addAttributeToFilter('parent_id', $rootCategoryId)
                        //->addFieldToFilter('store_id', $storeId)
                        ->getFirstItem();
                    
                    $cat_id = $category->getId();
                    if (!$cat_id) {
                        
                        $_description = "CTPCT$i";
                        $data = Mage::getSingleton('idpas400/db')
                                ->fetch_row("SELECT $_description FROM SROCTLP$i"
                                . " WHERE CTPCA$i ='$cat_slug'");
                        $name = ucwords(strtolower(trim($data[$_description])));
                        
                        $category->setUrlKey(strtolower($cat_slug));
                        $category->setName($name);
                        $category->setMetaDescription($name);
                        $category->setPath($parentCategory->getPath());
                        $category->setIsActive(1);
                        $category->setIsAnchor(0);
                        $category->setDisplayMode('PRODUCTS');
                        $category->setAttributeSetId($category->getDefaultAttributeSetId());
                        $category->save();

                        $cat_id = $category->getId();
                    }
                    $catids[] = $cat_id;
                }
                
            }
            if (!empty($catids)) {
//                if ($debug) 
//                    Mage::log('- categories ('.implode(',',$catids).')',null,$log);

                $_product->setCategoryIds($catids);
            }
            
            try {
                if (!$_product->getId()) {
                    $_product->getData('stock_item')->save();
                    $status = $_product->setStoreId($currentStore)->save();
                } else {
                    $status = $_product->setStoreId($currentStore)->save();
//                    $status = $_product->getResource()->save($_product);
                }
                
                // logging success of product save
//                if ($debug) 
//                Mage::log(($status
//                        ? '- Product saved.'
//                        : '- Prodct FAILED to save.'),null,$log);

            } catch(Exception $e) {
                Mage::log($e->getMessage(),null,$log);
            }
            Mage::app()->setCurrentStore($currentStore);
        }
        Mage::getModel('core/session')->setData('product_cron_sync',false);
    }
}
