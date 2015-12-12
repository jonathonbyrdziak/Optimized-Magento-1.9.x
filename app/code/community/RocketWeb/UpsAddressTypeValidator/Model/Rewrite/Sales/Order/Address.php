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
class RocketWeb_UpsAddressTypeValidator_Model_Rewrite_Sales_Order_Address extends Mage_Sales_Model_Order_Address {
    public function format($type)
    {
        return parent::format($type);
    }

    protected function isAdminOrderScreen() {
        return (Mage::app()->getStore()->isAdmin() && Mage::app()->getRequest()->getControllerName() == 'sales_order')?true:false;
    }
    protected function isUpsInvalid() {
        return ($this->getIsUpsInvalid())?true:false;
    }
    protected function isInvalidAddressWarningEnabled() {
        return Mage::getStoreConfig('rocketweb_addressvalidator/general/admin_warn_invalid_address');
    }
    protected function getInvalidAddressMessage($type) {
        if($type=='html') {
            return "<br/><strong style='color:red'>".Mage::helper('core')->__('UPS was unable to validate this address').'</strong>';
        }
        else {
            return "\n".Mage::helper('core')->__('UPS was unable to validate this address');
        }
    }
}
