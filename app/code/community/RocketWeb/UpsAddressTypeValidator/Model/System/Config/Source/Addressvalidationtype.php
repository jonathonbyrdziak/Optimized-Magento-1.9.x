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
class RocketWeb_UpsAddressTypeValidator_Model_System_Config_Source_Addressvalidationtype {
    public function toOptionArray()
    {
        return array(
            array('value' => RocketWeb_UpsAddressTypeValidator_Helper_Addressvalidation::NO_VALIDATION, 'label' => 'No address validation'),
            array('value' => RocketWeb_UpsAddressTypeValidator_Helper_Addressvalidation::WARN_CUSTOMER, 'label' => 'Warn customer about invalid addresses'),
            array('value' => RocketWeb_UpsAddressTypeValidator_Helper_Addressvalidation::DISALLOW_CHECKOUT, 'label' => 'Do not allow checkout if address is invalid'),
        );
    }
}