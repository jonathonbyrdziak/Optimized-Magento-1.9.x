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
class RocketWeb_UpsAddressTypeValidator_Model_System_Config_Source_Validationscope {

	/**
	 * Multi select options array
	 * @return array options
	 */
	public function toOptionArray()
	{
		return array(
				array('value' => 0, 'label' => Mage::helper('adminhtml')->__('Nowhere')),
				array('value' => RocketWeb_UpsAddressTypeValidator_Helper_Data::VALIDATION_SCOPE_FRONTEND, 'label' => Mage::helper('adminhtml')->__('Frontend')),
				array('value' => RocketWeb_UpsAddressTypeValidator_Helper_Data::VALIDATION_SCOPE_ADMIN, 'label' => Mage::helper('adminhtml')->__('Admin')),
				array('value' => RocketWeb_UpsAddressTypeValidator_Helper_Data::VALIDATION_SCOPE_OTHER, 'label' => Mage::helper('adminhtml')->__('Cron jobs, imports, CLI & other')),
			);
	}
}
