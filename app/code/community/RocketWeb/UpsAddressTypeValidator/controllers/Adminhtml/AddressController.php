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
class RocketWeb_UpsAddressTypeValidator_Adminhtml_AddressController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Validates the address from admin form
	 * @return echo
	 */
	public function validateAction()
	{
		// App
		$app = Mage::app();
		// Request
		$_request	= $app->getRequest();
		// UPS Config model
		$config 	= Mage::getModel('ups_address_validator/config');
		
		$_skip 		= false; // Var to skip some code with one of the params is missing

		// Html to return in json
		$html 		= '';
		// Init unordered list
		$html 		.= '<ul class="messages">';
		// Check if the extension is enabled
		if($config->isEnabled())
		{
			// Request params
			// Country
			$_countryCode = $_request->getParam('_country');
			// State / region_id
			$_state		= (int)$_request->getQuery('_state');
			// City
			$_city		= $_request->getQuery('_city');
			// Street
			$_street	= $_request->getQuery('_street');
			// City
			$_zip		= $_request->getQuery('_zip');
			
			// If the country code is missing or it is not US
			if(!$_countryCode || $_countryCode !== 'US')
			{
				// Add an error
				$html .= '<li class="' . Mage_Core_Model_Message::ERROR . '-msg">';
				$html .= Mage::helper('customer')->__("The address is invalid. The country code is either missing or different from United States.");
				$html .= '</li>';
				// And skip to the end of the method
				$_skip = true;
			}
			// If the state is missing
			if(!$_state && $_countryCode == 'US')
			{
				// Add error message
				$html .= '<li class="' . Mage_Core_Model_Message::ERROR . '-msg">';
				$html .= Mage::helper('customer')->__("The validation cannot be processed because the 'State/Province' field is either empty or incorrect.");
				$html .= '</li>';
				// And skip to the end
				$_skip = true;
			} else {
				// I fthe state cannot be found
				$state 		= Mage::getModel('directory/region')->load($_state);
				if(!$state)
				{
					// Add error
					$html .= '<li class="' . Mage_Core_Model_Message::ERROR . '-msg">';
					$html .= Mage::helper('customer')->__("The selected state could not be found.");
					$html .= '</li>';
					// And skip
					$_skip = true;
				}
			}
			// If the City is not set
			if(!$_city)
			{
				// Add error
				$html .= '<li class="' . Mage_Core_Model_Message::ERROR . '-msg">';
				$html .= Mage::helper('customer')->__("The validation could not be processed because the 'City' field is missing.");
				$html .= '</li>';
				// And go to the end
				$_skip = true;
			}
			// If the street address is not set
			if(!$_street)
			{
				// Add error
				$html .= '<li class="' . Mage_Core_Model_Message::ERROR . '-msg">';
				$html .= Mage::helper('customer')->__("The validation could not be processed because the 'Street Address' is not set.");
				$html .= '</li>';
				// And go to the end
				$_skip = true;
			}
			// If the Zip code is not set
			if(!$_zip)
			{
				// Add Error and go to the end
				$html .= '<li class="' . Mage_Core_Model_Message::ERROR . '-msg">';
				$html .= Mage::helper('customer')->__("The validation could not be processed because the Zip Code is missing or incorrect.");
				$html .= '</li>';
				$_skip = true;
			}
			// If skip is 'false'
			if(!$_skip) {
				// Get the rates model (Mage_Shipping_Model_Rate_Request)
				$rate 		= Mage::getModel('shipping/rate_request');
				// UPS Model
				$ups 		= Mage::getModel('ups_address_validator/usa_shipping_carrier_ups');
				// Set the region id to the model
				$rate->setDestRegionCode($state->getCode());
				// Set the destination city
				$rate->setDestCity($_city);
				// Set the country code
				$rate->setDestCountryId($_countryCode);
				// Set Zip code
				$rate->setDestPostcode($_zip);
				// Set the street address
				$rate->setDestStreet($_street);
				// Pass the model to the UPS request
				$ups->setRequest($rate);
				// Let's make the UPS request
				try {
					// Make the request to UPS
					$_response		= $ups->call();
					// Get the response status node
					$_node_status 	= $_response->getNode('Response/ResponseStatusDescription');
					// Get the message (Success, Failure)
					$_status		= (string)$_node_status;
					// Address classification node from UPS (Commercial, Residential, Unknown)
					$_node_type		= (string)$_response->getNode('AddressClassification/Description');
					// If the request status was successfull and its' type is not unknwon
					if($_status == "Success" && $_node_type !=='Unknown')
					{
						// Add the success message 
						$html .= '<li class="' . Mage_Core_Model_Message::SUCCESS . '-msg">';
						$html .= Mage::helper('customer')->__("The address is valid ($_node_type). ");
						$html .= '</li>';
					// Oherwise, f it is successfull, but the typw is unknown
					} elseif($_status == 'Success' && $_node_type == 'Unknown') {
						// Otherwise, add error message
						$html .= '<li class="' . Mage_Core_Model_Message::WARNING . '-msg">';
						$html .= Mage::helper('customer')->__("The address is invalid. ");
						$html .= '</li>';
					} else {
						$_error_description = (string)$_response->getNode('Response/Error/ErrorDescription');
						// Otherwise, add error message
						$html .= '<li class="' . Mage_Core_Model_Message::ERROR . '-msg">';
						$html .= Mage::helper('customer')->__("The address is invalid. ($_status: $_error_description).");
						$html .= '</li>';
					}
				} catch (Exception $e)
				{
					// Throw an exception message
					$html .= '<li class="' . Mage_Core_Model_Message::ERROR . '-msg">';
					$html .= Mage::helper('customer')->__("Error validating address (UPS Service connection failed: $e->getMessage() )");
					$html .= '</li>';
				}
			}
		// If the extension is disabled
		} else {
			$html .= '<li class="' . Mage_Core_Model_Message::ERROR . '-msg">';
			$html .= Mage::helper('customer')->__("The UPS Address Validator extension is disabled.");
			$html .= '</li>';
		}
		// Close the list
		$html 		.= '</ul>';
		// Echo the json
		echo json_encode(array("message" => $html));
	}
}