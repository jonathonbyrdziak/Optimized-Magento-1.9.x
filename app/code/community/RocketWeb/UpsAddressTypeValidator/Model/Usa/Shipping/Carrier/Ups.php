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
class RocketWeb_UpsAddressTypeValidator_Model_Usa_Shipping_Carrier_Ups
    extends RocketWeb_UpsAddressTypeValidator_Model_Usa_Shipping_Carrier_Ups_Pure
{
    const ADDRESS_TYPE_UNCLASSIFIED = 0;
    const ADDRESS_TYPE_COMMERCIAL = 1;
    const ADDRESS_TYPE_RESIDENTIAL = 2;


    /**
     * Prepare and set request to this instance
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Usa_Model_Shipping_Carrier_Ups
     */
    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        parent::setRequest($request);
        $this->_rawRequest->setDestCity($request->getDestCity());
        $this->_rawRequest->setDestStreet($request->getDestStreet());
        return $this;
    }

    protected function _getXAVXmlObject() {
        $url = $this->getConfigData('xav_xml_url');

        $this->setXMLAccessRequest();
        $xmlRequest=$this->_xmlAccessRequest;
        $r = $this->_rawRequest;

        $xmlRequest .= <<< XMLRequest
<?xml version="1.0"?>
<AddressValidationRequest xml:lang="en-US">
  <Request>
    <RequestAction>XAV</RequestAction>
    <RequestOption>3</RequestOption>
    <TransactionReference>
      <CustomerContext>Address Validation and Classification</CustomerContext>
      <XpciVersion>1.0</XpciVersion>
    </TransactionReference>
  </Request>
  <AddressKeyFormat>
    <AddressLine>{$r->getDestStreet()}</AddressLine>
    <PoliticalDivision2>{$r->getDestCity()}</PoliticalDivision2>
    <PoliticalDivision1>{$r->getDestRegionCode()}</PoliticalDivision1>
    <PostcodePrimaryLow>{$r->getDestPostal()}</PostcodePrimaryLow>
    <CountryCode>{$r->getDestCountry()}</CountryCode>
  </AddressKeyFormat>
</AddressValidationRequest>
XMLRequest;


        $xmlResponse = $this->_getCachedQuotes($xmlRequest);
        if ($xmlResponse === null) {
            $debugData = array('request' => $xmlRequest);
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $xmlResponse = curl_exec ($ch);
                $debugData['result'] = $xmlResponse;
                $this->_setCachedQuotes($xmlRequest, $xmlResponse);
            }
            catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                $this->_debug($debugData);
                return null;
            }
            $this->_debug($debugData);
        }

        $xml = new Varien_Simplexml_Config();
        $xml->loadString($xmlResponse);

        return $xml;
    }

    protected function _getAddressType() {
        if(!Mage::helper('ups_address_validator')->doValidateAddress())
            return self::ADDRESS_TYPE_UNCLASSIFIED;
        $xml = $this->_getXAVXmlObject();
        if(is_null($xml)) {
            return self::ADDRESS_TYPE_UNCLASSIFIED;
        }
        else {
            $arr = $xml->getXpath("//AddressValidationResponse/Response/ResponseStatusCode/text()");
            $success = (int)$arr[0];
            if ($success===1) {
                $arr = $xml->getXpath("//AddressValidationResponse/AddressClassification/Code/text()");
                return (int) $arr[0];
            }
            else {
                return self::ADDRESS_TYPE_UNCLASSIFIED;
            }
        }
    }


    /**
     * Get xml rates
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _getXmlQuotes()
    {
        if(Mage::getStoreConfig('shipping/shipusa/active')) {
            return $this->_getXmlQuotesForWebshopapps();
        }
        else {
            return $this->_getXmlQuotesStandard();
        }
    }

    /**
     * Make the request to UPS
     * @return Varien_Simplexml_Config
     */
    public function call()
    {
      return $this->_getXAVXmlObject();
    }

    protected function _getXmlQuotesStandard() {
        if(!Mage::getStoreConfig('carriers/ups/ups_xml_override_defaults')) {
            return parent::_getXmlQuotes();
        }
        $addressType = $this->_getAddressType();

        $url = $this->getConfigData('gateway_xml_url');

        $this->setXMLAccessRequest();
        $xmlRequest=$this->_xmlAccessRequest;

        $r = $this->_rawRequest;
        $params = array(
            'accept_UPS_license_agreement' => 'yes',
            '10_action'      => $r->getAction(),
            '13_product'     => $r->getProduct(),
            '14_origCountry' => $r->getOrigCountry(),
            '15_origPostal'  => $r->getOrigPostal(),
            'origCity'       => $r->getOrigCity(),
            'origRegionCode' => $r->getOrigRegionCode(),
            '19_destPostal'  => Mage_Usa_Model_Shipping_Carrier_Abstract::USA_COUNTRY_ID == $r->getDestCountry() ?
                substr($r->getDestPostal(), 0, 5) :
                $r->getDestPostal(),
            '22_destCountry' => $r->getDestCountry(),
            'destRegionCode' => $r->getDestRegionCode(),
            '23_weight'      => $r->getWeight(),
            '47_rate_chart'  => $r->getPickup(),
            '48_container'   => $r->getContainer(),
            '49_residential' => $r->getDestType(),
        );

        if ($params['10_action'] == '4') {
            $params['10_action'] = 'Shop';
            $serviceCode = null; // Service code is not relevant when we're asking ALL possible services' rates
        } else {
            $params['10_action'] = 'Rate';
            $serviceCode = $r->getProduct() ? $r->getProduct() : '';
        }
        $serviceDescription = $serviceCode ? $this->getShipmentByCode($serviceCode) : '';

        $xmlRequest .= <<< XMLRequest
<?xml version="1.0"?>
<RatingServiceSelectionRequest xml:lang="en-US">
  <Request>
    <TransactionReference>
      <CustomerContext>Rating and Service</CustomerContext>
      <XpciVersion>1.0</XpciVersion>
    </TransactionReference>
    <RequestAction>Rate</RequestAction>
    <RequestOption>{$params['10_action']}</RequestOption>
  </Request>
  <PickupType>
          <Code>{$params['47_rate_chart']['code']}</Code>
          <Description>{$params['47_rate_chart']['label']}</Description>
  </PickupType>

  <Shipment>
XMLRequest;

        if ($serviceCode !== null) {
            $xmlRequest .= "<Service>" .
                "<Code>{$serviceCode}</Code>" .
                "<Description>{$serviceDescription}</Description>" .
                "</Service>";
        }

        $xmlRequest .= <<< XMLRequest
      <Shipper>
XMLRequest;

        if ($this->getConfigFlag('negotiated_active') && ($shipper = $this->getConfigData('shipper_number')) ) {
            $xmlRequest .= "<ShipperNumber>{$shipper}</ShipperNumber>";
        }

        if ($r->getIsReturn()) {
            $shipperCity = '';
            $shipperPostalCode = $params['19_destPostal'];
            $shipperCountryCode = $params['22_destCountry'];
            $shipperStateProvince = $params['destRegionCode'];
        } else {
            $shipperCity = $params['origCity'];
            $shipperPostalCode = $params['15_origPostal'];
            $shipperCountryCode = $params['14_origCountry'];
            $shipperStateProvince = $params['origRegionCode'];
        }

        $xmlRequest .= <<< XMLRequest
      <Address>
          <City>{$shipperCity}</City>
          <PostalCode>{$shipperPostalCode}</PostalCode>
          <CountryCode>{$shipperCountryCode}</CountryCode>
          <StateProvinceCode>{$shipperStateProvince}</StateProvinceCode>
      </Address>
    </Shipper>
    <ShipTo>
      <Address>
          <PostalCode>{$params['19_destPostal']}</PostalCode>
          <CountryCode>{$params['22_destCountry']}</CountryCode>
          <StateProvinceCode>{$params['destRegionCode']}</StateProvinceCode>
XMLRequest;

        if($addressType == self::ADDRESS_TYPE_RESIDENTIAL) {
            $xmlRequest .= "<ResidentialAddressIndicator></ResidentialAddressIndicator>";
        }

        if($addressType == self::ADDRESS_TYPE_UNCLASSIFIED && $r->getDestCountry() == 'US') {
            $value = '';
            switch(Mage::getStoreConfig('carriers/ups/dest_type'))
            {
                case 'RES':
                    $value = '1';
                    $xmlRequest .= "<ResidentialAddressIndicator>$value</ResidentialAddressIndicator>";
                    break;
                case 'COM':
                    $value = '2';
                    break;
            }
        }
        $session = Mage::getSingleton("core/session", array("name"=>"frontend"));
        $session->unsetData('ResidentialIndicator');
        $session->setData('ResidentialIndicator', $addressType);
        $session->unsetData('invalid_address');

        if($addressType == self::ADDRESS_TYPE_UNCLASSIFIED && $r->getDestCountry() == 'US') {
            $session->setData('invalid_address',1);
        }


        $xmlRequest .= <<< XMLRequest
      </Address>
    </ShipTo>


    <ShipFrom>
      <Address>
          <PostalCode>{$params['15_origPostal']}</PostalCode>
          <CountryCode>{$params['14_origCountry']}</CountryCode>
          <StateProvinceCode>{$params['origRegionCode']}</StateProvinceCode>
      </Address>
    </ShipFrom>

    <Package>
      <PackagingType><Code>{$params['48_container']}</Code></PackagingType>
      <PackageWeight>
         <UnitOfMeasurement><Code>{$r->getUnitMeasure()}</Code></UnitOfMeasurement>
        <Weight>{$params['23_weight']}</Weight>
      </PackageWeight>
    </Package>
XMLRequest;
        if ($this->getConfigFlag('negotiated_active')) {
            $xmlRequest .= "<RateInformation><NegotiatedRatesIndicator/></RateInformation>";
        }

        $xmlRequest .= <<< XMLRequest
  </Shipment>
</RatingServiceSelectionRequest>
XMLRequest;

        $xmlResponse = $this->_getCachedQuotes($xmlRequest);
        if ($xmlResponse === null) {
            $debugData = array('request' => $xmlRequest);
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (boolean)$this->getConfigFlag('mode_xml'));
                $xmlResponse = curl_exec ($ch);

                $debugData['result'] = $xmlResponse;
                $this->_setCachedQuotes($xmlRequest, $xmlResponse);
            }
            catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                $xmlResponse = '';
            }
            $this->_debug($debugData);
        }

        return $this->_parseXmlResponse($xmlResponse);
    }

    protected function _getXmlQuotesForWebshopapps() {
        $url = $this->getConfigData('gateway_xml_url');

        $addressType = $this->_getAddressType();

        $this->setXMLAccessRequest();
        $xmlRequest=$this->_xmlAccessRequest;

        $r = $this->_rawRequest;

        $params = array(
            'accept_UPS_license_agreement' => 'yes',
            '10_action'      => $r->getAction(),
            '13_product'     => $r->getProduct(),
            '14_origCountry' => $r->getOrigCountry(),
            '15_origPostal'  => $r->getOrigPostal(),
            'origCity'       => $r->getOrigCity(),
            'origRegionCode' => $r->getOrigRegionCode(),
            '19_destPostal'  => Mage_Usa_Model_Shipping_Carrier_Abstract::USA_COUNTRY_ID == $r->getDestCountry() ?
                substr($r->getDestPostal(), 0, 5) :
                $r->getDestPostal(),
            '22_destCountry' => $r->getDestCountry(),
            'destRegionCode' => $r->getDestRegionCode(),
            '23_weight'      => $r->getWeight(),
            '25_length'      => $r->getLength(),
            '26_width'       => $r->getWidth(),
            '27_height'      => $r->getHeight(),
            '47_rate_chart'  => $r->getPickup(),
            '48_container'   => $r->getContainer(),
            '49_residential' => $r->getDestType(),
        );
        if ($params['10_action'] == '4') {
            $params['10_action'] = 'Shop';
            $serviceCode = null; // Service code is not relevant when we're asking ALL possible services' rates
        } else {
            $params['10_action'] = 'Rate';
            $serviceCode = $r->getProduct() ? $r->getProduct() : '';
        }
        $serviceDescription = $serviceCode ? $this->getShipmentByCode($serviceCode) : '';

        $xml = new SimpleXMLElement('<?xml version = "1.0"?><RatingServiceSelectionRequest xml:lang="en-US"/>');

        $request = $xml->addChild('Request');
        $transReference = $request->addChild('TransactionReference');
        $transReference->addChild('CustomerContext','Rating and Service');
        $transReference->addChild('XpciVersion','1.0');
        $request->addChild('RequestAction','Rate');
        $request->addChild('RequestOption',$params['10_action']);

        $pickupType = $xml->addChild('PickupType');
        $pickupType->addChild('Code',$params['47_rate_chart']['code']);
        $pickupType->addChild('Description',$params['47_rate_chart']['label']);

        $shipment = $xml->addChild('Shipment');

        // UPS Date shipping addition
        if (is_object($this->_upsTransitModel) &&
            $this->_upsTransitModel->isSaturday()) {

            $shipmentServiceOptions = $shipment->addChild('ShipmentServiceOptions');
            $shipmentServiceOptions->addChild('SaturdayDeliveryIndicator');
        }


        if ($serviceCode !== null) {
            $service = $shipment->addChild('Service');
            $service->addChild('Code',$serviceCode);
            $service->addChild('Description',$serviceDescription);
        }


        if (!Mage::helper('wsacommon')->checkItems('c2hpcHBpbmcvc2hpcHVzYS9zaGlwX29uY2U=',
            'aWdsb29tZQ==','c2hpcHBpbmcvc2hpcHVzYS9zZXJpYWw=')) {
            Mage::log('U2VyaWFsIEtleSBJcyBOT1QgVmFsaWQgZm9yIFdlYlNob3BBcHBzIERpbWVuc2lvbmFsIFNoaXBwaW5n');
            return Mage::getModel('shipping/rate_result');
        }


        $shipper = $shipment->addChild('Shipper');

        // WSA CHANGE
        if ($this->_request->getUpsShipperNumber()) {
            $shipperNum = $this->_request->getUpsShipperNumber();
        } else {
            $shipperNum = $this->getConfigData('shipper_number');
        }

        if ($this->getConfigFlag('negotiated_active') && ($shipperNum != '') ) {
            $shipper->addChild('ShipperNumber',$shipperNum);
        }
        $address = 	$shipper->addChild('Address');
        $address->addChild('City',$params['origCity']);
        $address->addChild('PostalCode',$params['15_origPostal']);
        $address->addChild('CountryCode',$params['14_origCountry']);
        $address->addChild('StateProvinceCode',$params['origRegionCode']);

        $shipTo = $shipment->addChild('ShipTo');
        $address = 	$shipTo->addChild('Address');
        $address->addChild('PostalCode',$params['19_destPostal']);
        $address->addChild('CountryCode',$params['22_destCountry']);
        $address->addChild('StateProvinceCode',$params['destRegionCode']);

        if($addressType == self::ADDRESS_TYPE_RESIDENTIAL) {
            $address->addChild('ResidentialAddressIndicator','');
        }

        if($addressType == self::ADDRESS_TYPE_UNCLASSIFIED && $r->getDestCountry() == 'US') {
            $value = '';
            switch(Mage::getStoreConfig('carriers/ups/dest_type'))
            {
                case 'RES':
                    $value = '1';
                    $address->addChild('ResidentialAddressIndicator',$value);
                    break;
                case 'COM':
                    $value = '2';
                    break;
            }
        }
        $session = Mage::getSingleton("core/session", array("name"=>"frontend"));
        $session->unsetData('ResidentialIndicator');
        $session->setData('ResidentialIndicator', $addressType);
        $session->unsetData('invalid_address');

        if($addressType == self::ADDRESS_TYPE_UNCLASSIFIED && $r->getDestCountry() == 'US') {
            $session->setData('invalid_address',1);
        }

        $shipFrom = $shipment->addChild('ShipFrom');
        $address = 	$shipFrom->addChild('Address');
        $address->addChild('PostalCode',$params['15_origPostal']);
        $address->addChild('CountryCode',$params['14_origCountry']);
        $address->addChild('StateProvinceCode',$params['origRegionCode']);

        $handProdFee=0;
        $this->_addAllPackages($shipment,$handProdFee);

        if ($this->getConfigFlag('negotiated_active')) {
            $rateInfo = $shipment->addChild('RateInformation');
            $rateInfo->addChild('NegotiatedRatesIndicator');
        }

        $debugData = array(	'request' => Mage::helper('shipusa')->formatXML($xml->asXML()),
            'handling_fee' => $handProdFee);
        $xmlRequest.=$xml->asXML();


        $xmlResponse = $this->_getCachedQuotes($xmlRequest);
        if ($xmlResponse === null) {

            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (boolean)$this->getConfigFlag('mode_xml'));
                $xmlResponse = curl_exec ($ch);
                $this->_setCachedQuotes($xmlRequest,  Mage::helper('shipusa')->formatXML($xmlResponse));
                $debugData['result'] = Mage::helper('shipusa')->formatXML($xmlResponse);
            } catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                $xmlResponse = '';
                if ($this->getDebugFlag()) {
                    Mage::helper('wsacommon/log')->postMajor('usashipping','UPS Exception Raised',$debugData);
                }
            }
        } else {
            $debugData['result'] = $xmlResponse;
            $debugData['cached'] = 'true';
        }
        $this->_debug($debugData);
        if ($this->getDebugFlag()) {
            Mage::helper('wsalogger/log')->postInfo('usashipping','UPS Request/Response',$debugData);
        }

        return $this->_parseDimResponse($debugData,$xmlResponse,$handProdFee);
    }
}
