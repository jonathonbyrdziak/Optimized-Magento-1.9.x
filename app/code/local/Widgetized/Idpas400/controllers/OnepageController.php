<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OnepageController
 *
 * @author Jonathon
 */
require_once 'Mage/Checkout/controllers/OnepageController.php';
require_once 'OnePica/AvaTax/controllers/OnepageController.php';
class Widgetized_Idpas400_OnepageController extends OnePica_AvaTax_OnepageController {
    
    /**
     * Returns the shipping mthod options HTML in
     * checkout onepage, including a custom warning, 
     * if applicable
     * @return string 
     */
    protected function _getShippingMethodsHtml()
    {
        // Load the extension's Data.php helper
        $helper = Mage::helper('ups_address_validator');
        // Load current customer's session
        $session = Mage::getSingleton("core/session", array("name"=>"frontend"));
        // Switch between choice in Admin:
        // 0 - No validation
        // 1 - Warn customer
        // 2 - Do not allow checkout if faddress is invalid
        switch(Mage::getStoreConfig('rocketweb_addressvalidator/general/validation_type')) {
            // For Warn customer (1)
            case RocketWeb_UpsAddressTypeValidator_Helper_Addressvalidation::WARN_CUSTOMER:
                // Get whatever the shipping methods HTML is
                $shippingMethodsHtml = parent::_getShippingMethodsHtml();
                // If the controller is in the scope (frontend in this case)
                if($helper->doValidateAddress()) {
                    // Load customer's shipping address
                    $shipping         = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
                    // Get Address type
                    $ups_address_type = RocketWeb_UpsAddressTypeValidator_Helper_Data::getAddressTypeFromAddress($shipping);
                    // Check if the address is valid by calling UPS
                    $is_valid_address = (is_numeric($ups_address_type) ? true : false);
                    // If UPS couldn't determine the address type and the country is US
                    if($shipping->getCountryId() == 'US' && ($session->getData('invalid_address') || !$is_valid_address)) {
                        // Prepend the warning message to shipping methods HTML
                        $shippingMethodsHtml = '<strong>'.Mage::getStoreConfig('rocketweb_addressvalidator/general/customer_warning_message').'</strong>'.$shippingMethodsHtml;
                    }
                }
                return $shippingMethodsHtml;
                break;
            // For Stopping the checkout (2)
            case RocketWeb_UpsAddressTypeValidator_Helper_Addressvalidation::DISALLOW_CHECKOUT:
                // If the controller is in the scope (frontend in this case)
                if($helper->doValidateAddress()) {
                    // Load customer's shipping address
                    $shipping         = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
                    // Get Address type
                    $ups_address_type = RocketWeb_UpsAddressTypeValidator_Helper_Data::getAddressTypeFromAddress($shipping);
                    // Check if the address is valid by calling UPS
                    $is_valid_address = (is_numeric($ups_address_type) ? true : false);
                    // Add type to session
                    $session->setVisitorData('ups_address_type', $ups_address_type);
                    // If the country is US and the address is invalid
                    if($shipping->getCountryId() == 'US' && ($session->getData('invalid_address') || !$is_valid_address)) {
                        // Replace the actual shipping HTML opion with a message
                        $shippingMethodsHtml = '<strong>'.Mage::getStoreConfig('rocketweb_addressvalidator/general/checkout_stop_message').'</strong>';
                        
                        return $shippingMethodsHtml;
                    } else {
                        return parent::_getShippingMethodsHtml();
                    }
                } else {
                    return parent::_getShippingMethodsHtml();
                }
                break;

            case RocketWeb_UpsAddressTypeValidator_Helper_Addressvalidation::NO_VALIDATION:
                return parent::_getShippingMethodsHtml();

            default:
                return parent::_getShippingMethodsHtml();
        }
    }
    
    /**
     * FOR AVATAX
     * Initialize shipping information
     */
    public function indexAction()
    {         
        $session = Mage::getSingleton('checkout/session');
        $session->setPostType('onepage');
        parent::indexAction();		
    }
    
    /**
     * 
     */
    public function savePaymentAction() {
        // associate school with order
        $school_id = $this->getRequest()->getParam('donate_to_school',false);
        if ($school_id) {
            try {
                $orderOption = Mage::getModel('idpas400/order');
                $orderOption->setOrderId( $this->getOnepage()->getQuote()->getId() );
                $orderOption->setKey('shool_id');
                $orderOption->setValue($school_id);
                $orderOption->save();
            } catch(Exception $e) {
                Mage::log('Failed to save: '.$school_id,null,'sales_order_school.log');
            }
        }
        
        // subscribe to newsletter
        $special_offers = $this->getRequest()->getParam('special_offers',false);
        $customer = $this->getOnepage()->getQuote()->getCustomer();
        if ($special_offers) {
            // subscribe to magento
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
            if($subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED &&
                    $subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED) {
                $subscriber->setImportMode(true)->subscribe($customer->getEmail());
            }
            // subscribe to mailchimp
            Mage::helper('monkey')->subscribeToMainList($subscriber);
        }
        
        // apply the discount if it's there
        Mage::helper('idpas400')->apply_coupon(
                $this->getOnepage()->getQuote(),
                $this->getRequest());
        
        // parent::savePaymentAction();
        if ($this->_expireAjax()) {
            return;
        }
        try {
            if (!$this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }

            $redirectUrl = false;
            $data = $this->getRequest()->getPost('payment', array());
            
//            This is a saved card action
            if (isset($data['saved_card_id']) && $data['saved_card_id'] && $data['saved_card_id']!='new') {
                $quote = $this->getOnepage()->getQuote();
                $quote->setUseSavedCard( $data['saved_card_id'] );
                $quote->save();
                
                $payment = $quote->getPayment();
                $payment->setMethod('revolution_saved');
                $payment->save();
                
                Mage::getSingleton('checkout/session')
                    ->setStepData('payment', 'complete', true)
                    ->setStepData('review', 'allow', true);

//            This is a brand new card action (default)
            } else {
                $result = $this->getOnepage()->savePayment($data);
                if ($this->getRequest()->getParam('save_credit_card','off')=='on') {
                    Mage::helper('level3')->savePostedCard();
                }
                $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            }

            // get section and redirect data
            if (empty($result['error']) && !$redirectUrl) {
                $this->loadLayout('checkout_onepage_review');
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
            }
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            Mage::logException($e);
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    /**
     * 
     */
    public function createAccountAction() {
        $this->loadLayout();
        
        $layout = $this->getLayout();
        $block = $layout->createBlock('idpas400/register');
        $block->setTemplate('customerattribute/customer/form/register.phtml');
        
        $childrenblock = new Magestore_Customerattribute_Block_Customer_Form();
        $childrenblock->setFormCode('customer_account_create');
        $childrenblock->setEntityModelClass('customer/customer');
        $childrenblock->setParentBlock($block);
        $childrenblock->loadTemplate();
        $childrenblock->setTemplate('customerattribute/customer/form/userattributes.phtml');
        
        $block->setChild('customer_form_user_attributes', $childrenblock);
        
        echo $block->toHtml();
    }

    /**
     * Create order action
     */
    public function saveOrderAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*');
            return;
        }

        if ($this->_expireAjax()) {
            return;
        }

        $result = array();
        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $diff = array_diff($requiredAgreements, $postedAgreements);
                if ($diff) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }
            
            $data = $this->getRequest()->getPost('payment', array());
            
//            This is a saved card action
            if (isset($data['saved_card_id']) && $data['saved_card_id']!='new') {
                $quote = $this->getOnepage()->getQuote();
                $quote->setUseSavedCard( $data['saved_card_id'] );
                $quote->setTotalsCollectedFlag(true);
                $quote->save();
                
//            This is a brand new card action (default)
            } elseif ($data) {
                $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                    | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                    | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }

            $this->getOnepage()->saveOrder();
            
            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error']   = false;
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $result['error_messages'] = $message;
            }
            $result['goto_section'] = 'payment';
            $result['update_section'] = array(
                'name' => 'payment-method',
                'html' => $this->_getPaymentMethodsHtml()
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            $gotoSection = $this->getOnepage()->getCheckout()->getGotoSection();
            if ($gotoSection) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()->getCheckout()->setGotoSection(null);
            }
            $updateSection = $this->getOnepage()->getCheckout()->getUpdateSection();
            if ($updateSection) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                        'name' => $updateSection,
                        'html' => $this->$updateSectionFunction()
                    );
                }
                $this->getOnepage()->getCheckout()->setUpdateSection(null);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }
        $this->getOnepage()->getQuote()->save();
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Order success action
     */
    public function successAction()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }

        // ----- INJECTED OBSERVER EVENT
        Mage::dispatchEvent('checkout_onepage_controller_success_before_action', array('order_ids' => array($lastOrderId)));
        // ------ END
        
        $session->clear();
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
        $this->renderLayout();
    }
}
