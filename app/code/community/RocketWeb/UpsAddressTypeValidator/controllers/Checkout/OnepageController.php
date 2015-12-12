<?php
/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   RocketWeb
 * @package    RocketWeb_UpsAddressTypeValidator
 * @copyright  Copyright (c) 2013 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */
require_once 'Mage/Checkout/controllers/OnepageController.php';
class RocketWeb_UpsAddressTypeValidator_Checkout_OnepageController extends Mage_Checkout_OnepageController {
    
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
}