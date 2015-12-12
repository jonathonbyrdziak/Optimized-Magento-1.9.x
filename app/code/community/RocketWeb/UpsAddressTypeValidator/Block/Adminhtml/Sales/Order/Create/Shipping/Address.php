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

class RocketWeb_UpsAddressTypeValidator_Block_Adminhtml_Sales_Order_Create_Shipping_Address extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Address
{
	/**
     * Return header text
     *
     * @return string
     */
    public function getHeaderText()
    {
       	if(Mage::getModel('ups_address_validator/config')->isEnabled())
        {
	        $_text = parent::getHeaderText();
	        $_text .= $this->getLayout()->createBlock('adminhtml/widget_button')
	                ->setData(array(
	                    'label'  => Mage::helper('customer')->__('Validate Address'),
	                    'id'     => 'rw_validate_address'.$this->getTemplatePrefix(),
	                    'name'   => 'rw_validate_address',
	                    'element_name' => 'rw_validate_address',
	                    'class'  => 'scalable save',
	                    'style' => 'float:right; margin-left: 10px;',
	                    'onclick'=> 'return RocketWeb.Address.validate(\'order-shipping_address\',\'' .Mage::helper('adminhtml')->getUrl('adminhtml/address/validate') . '\', \'order\');'
	                ))->setAfter("-")->toHtml();
	        return $_text;
        }
        return parent::getHeaderText();
    }
}