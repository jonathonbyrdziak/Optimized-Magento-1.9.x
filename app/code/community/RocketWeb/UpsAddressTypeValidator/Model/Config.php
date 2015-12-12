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
class RocketWeb_UpsAddressTypeValidator_Model_Config extends Mage_Core_Model_Config_Data
{

    CONST XML_PATH_SETTINGS_UPS_IS_ENABLED                                  = 'carriers/ups/active';
    CONST XML_PATH_SETTINGS_UPS_XML_ACCESS_LICENCE_NUMBER                   = 'carriers/ups/ups_xml_access_license_number';
    CONST XML_PATH_SETTINGS_UPS_XML_USER_ID                                 = 'carriers/ups/ups_xml_user_id';
    CONST XML_PATH_SETTINGS_UPS_XML_PASSWORD                                = 'carriers/ups/ups_xml_password';
    CONST XML_PATH_SETTINGS_UPS_XML_OVERRIDE_DEFAULTS_SWITCH                = 'carriers/ups/ups_xml_override_defaults';
    CONST XML_PATH_SETTINGS_UPS_XML_RESIDENTIAL_INDICATOR_SWITCH            = 'carriers/ups/ups_xml_add_indicator';
    CONST XML_PATH_SETTINGS_UPS_XML_RESIDENTIAL_INDICATOR_OTHER_SWITCH      = 'carriers/ups/ups_xml_add_indicator_all_other';
    CONST XML_PATH_SETTINGS_UPS_XML_RESIDENTIAL_INDICATOR                   = 'carriers/ups/ups_xml_residential_indicator';
    CONST XML_PATH_SETTINGS_UPS_XML_COMMERCIAL_INDICATOR                    = 'carriers/ups/ups_xml_commercial_indicator';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        $enabled = Mage::getStoreConfig(self::XML_PATH_SETTINGS_UPS_IS_ENABLED, $this->getStoreId());
        return $enabled;
    }

    /**
     * @return string
     */
    public function getAccessLicenceNumber()
    {
        $str = Mage::getStoreConfig(self::XML_PATH_SETTINGS_UPS_XML_ACCESS_LICENCE_NUMBER, $this->getStoreId());
        return $str;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        $str = Mage::getStoreConfig(self::XML_PATH_SETTINGS_UPS_XML_USER_ID, $this->getStoreId());
        return $str;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        $str = Mage::getStoreConfig(self::XML_PATH_SETTINGS_UPS_XML_PASSWORD, $this->getStoreId());
        return $str;
    }

    /**
     * @return bool
     */
    public function overrideDefaults()
    {
        return Mage::getStoreConfig(self::XML_PATH_SETTINGS_UPS_XML_OVERRIDE_DEFAULTS_SWITCH, $this->getStoreId());
    }

    /**
     * @return bool
     */
    public function enableIndicator()
    {
        return Mage::getStoreConfig(self::XML_PATH_SETTINGS_UPS_XML_RESIDENTIAL_INDICATOR_SWITCH, $this->getStoreId());
    }

    /**
     * @return bool
     */
    public function enableIndicatorForOthers()
    {
        return Mage::getStoreConfig(self::XML_PATH_SETTINGS_UPS_XML_RESIDENTIAL_INDICATOR_OTHER_SWITCH, $this->getStoreId());
    }

    /**
     * @return string
     */
    public function getResidentialIndicator()
    {
        return Mage::getStoreConfig(self::XML_PATH_SETTINGS_UPS_XML_RESIDENTIAL_INDICATOR, $this->getStoreId());
    }

    /**
     * @return string
     */
    public function getCommercialIndicator()
    {
        return Mage::getStoreConfig(self::XML_PATH_SETTINGS_UPS_XML_COMMERCIAL_INDICATOR, $this->getStoreId());
    }

    public function isInvalidAddressWarningEnabled() {
        return Mage::getStoreConfig('rocketweb_addressvalidator/general/admin_warn_invalid_address', $this->getStoreId());
    }

    public function getAddressValidationType()
    {
        return Mage::getStoreConfig('rocketweb_addressvalidator/general/validation_type', $this->getStoreId());
    }
}