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
class RocketWeb_UpsAddressTypeValidator_Helper_Data extends Mage_Core_Helper_Abstract {

    const VALIDATION_SCOPE_FRONTEND = 1;
    const VALIDATION_SCOPE_ADMIN = 2;
    const VALIDATION_SCOPE_OTHER = 3;

    public static function getAddressTypeFromAddress($address) {
        $country_id = $address->getCountryId();
        if ($country_id == 'US') {
            $region = $address->getRegion();
            $stateCode = Mage::getModel('directory/region')->loadByName($region, $country_id)->getCode();
            $city = $address->getCity();
            $zip = $address->getPostcode();
            $street = $address->getStreet();
            $rate = Mage::getModel('shipping/rate_request');
            $ups = Mage::getModel('ups_address_validator/usa_shipping_carrier_ups');
            // Set the region id to the model
            $rate->setDestRegionCode($stateCode);
            // Set the destination city
            $rate->setDestCity($city);
            // Set the country code
            $rate->setDestCountryId($country_id);
            // Set Zip code
            $rate->setDestPostcode($zip);
            // Set the street address
            $rate->setDestStreet($street[0]);
            // Pass the model to the UPS request
            $ups->setRequest($rate);
            try {
                // Make the request to UPS
                $_response = $ups->call();
                // Get the response status node
                $_node_status = $_response->getNode('Response/ResponseStatusDescription');
                // Compare the political division
                $_pol_division = (string) trim($_response->getNode('AddressKeyFormat/PoliticalDivision1'));
                // Get the message (Success, Failure)
                $_status = (string) $_node_status;
                // Address classification node from UPS (Commercial, Residential, Unknown)
                $_node_type = (string) $_response->getNode('AddressClassification/Code');
                // If the request status was successful and its' type is not unknown
                if ($_status == "Success" && $_node_type == 0 && ($_pol_division == $stateCode)) {
                    return '';
                } elseif ($_status == 'Success' && $_node_type !== 0 && ($_pol_division == $stateCode)) {
                    return (int) $_node_type;
                } elseif ($_status == 'Success' && ($_pol_division !== $stateCode)) {
                    return '';
                } else {
                    $_error_description = (string) $_response->getNode('Response/Error/ErrorDescription');
                    return Mage::helper("customer")->__("Error: %s", $_error_description);
                }
            } catch (Exception $e) {
                return Mage::helper("customer")->__("Exception: %s", $e->getMessage());
            }
        } else {
            return Mage::helper('customer')->__("Outside United States (%s)", $country_id);
        }
    }

    private function getCurrentActionScope() {
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            return self::VALIDATION_SCOPE_OTHER;
        }

        if (Mage::app()->getStore()->isAdmin() || Mage::getDesign()->getArea() == 'adminhtml') {
            return self::VALIDATION_SCOPE_ADMIN;
        } else {
            return self::VALIDATION_SCOPE_FRONTEND;
        }
    }

    public function doValidateAddress() {
        $config = Mage::getStoreConfig('rocketweb_addressvalidator/general/run_validation_scope');
        $scopes = explode(',', $config);
        if (in_array($this->getCurrentActionScope(), $scopes)) {
            return true;
        }
        return false;
    }

}
