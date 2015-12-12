<?php

/**
 * UPS shipping implementation
 *
 * @category   Mage
 * @package    Mage_Usa
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Widgetized_Idpas400_Model_Ups extends Mage_Usa_Model_Shipping_Carrier_Ups{
    /**
     * 0 = turned off
     * 1 = out
     * 2 = output and die
     */
    const WIDGETIZED_DEBUG = 0;
    
    /**
     * Prepare and set request to this instance
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Usa_Model_Shipping_Carrier_Ups
     */
    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        $quote = $request->getQuote();
        if (!$quote || !$quote->getId()) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        if (!$quote || !$quote->getId() && Mage::registry('recurring_order', false)) {
            $quote = Mage::registry('recurring_order')->getQuote();
        }
        
        if ($quote && $quote->getId()) {
            Mage::unregister('recurring_quote');
            Mage::register('recurring_quote', $quote);
        }
        
        parent::setRequest($request);
        
        if (!$quote || !$quote->getId()) return $this;
        
        // last attempt at checking this damn thing
        if (!$quote->getShippingAddress()->getResidentialIndicator()) {
            Mage::dispatchEvent('widgetized_validate_address', array(
                $this->_eventObject => $this,
                'order' => $quote
                ));
        }
        
        // Manually overriding the residential indicator
        if ($indicator = $quote->getShippingAddress()->getResidentialIndicator()) {
            $this->_rawRequest->setDestType($indicator);
        }
        
        return $this;
    }

    /**
     * Get xml rates
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _getXmlQuotes()
    {
        $url = $this->getConfigData('gateway_xml_url');
        if (!$url) {
            $url = $this->_defaultUrls['Rate'];
        }

        $this->setXMLAccessRequest();
        $access = $xmlRequest = $this->_xmlAccessRequest;

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
            '49_residential' => $r->getDestType()
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
          <ResidentialAddress>{$params['49_residential']}</ResidentialAddress>
          <StateProvinceCode>{$params['destRegionCode']}</StateProvinceCode>
XMLRequest;

          $xmlRequest .= ($params['49_residential']==='01'
                  ? "<ResidentialAddressIndicator>{$params['49_residential']}</ResidentialAddressIndicator>"
                  : ''
          );

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
XMLRequest;
        
          
        $quote = Mage::registry('recurring_quote');
        if (!$quote) {
$xmlRequest .= <<< XMLRequest
    <Package>
      <PackagingType>
          <Code>{$params['48_container']}</Code>
      </PackagingType>
      <PackageWeight>
         <UnitOfMeasurement><Code>{$r->getUnitMeasure()}</Code></UnitOfMeasurement>
        <Weight>{$params['23_weight']}</Weight>
      </PackageWeight>
    </Package>
XMLRequest;
        
        } else {
            foreach ($quote->getAllVisibleItems() as $_item) {
//                $_product = Mage::getModel('catalog/product')->load($_item->getProductId());
                for ($i=0; $i<$_item->getQty(); $i++) {
$xmlRequest .= <<< XMLRequest
    <Package>
      <PackagingType>
          <Code>02</Code>
          <Description>Manufacturers Packaging</Description>
      </PackagingType>
      <PackageWeight>
         <UnitOfMeasurement><Code>{$r->getUnitMeasure()}</Code></UnitOfMeasurement>
         <Weight>{$_item->getWeight()}</Weight>
      </PackageWeight>
    </Package>
XMLRequest;
                }
            }
        }
        
        
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
        
        $this->readibleDebugOutput($xmlRequest,$access,$xmlResponse);

        return $this->_parseXmlResponse($xmlResponse);
    }
    
    /**
     * DEBUGGING OUTPUT
     * 
     * @param type $xmlRequest
     * @param type $access
     * @param type $response
     */
    protected function readibleDebugOutput( $xmlRequest,$access,$response ) {
        if (self::WIDGETIZED_DEBUG>0) 
        {
            echo '<b>request</b><pre>';
            $xmlRequest = str_replace($access,'',$xmlRequest);
            print_r(htmlspecialchars($xmlRequest));
            echo '</pre>';

            echo '<b>response</b><pre>';
            $response = htmlspecialchars(str_replace('>',">\r",$response));
            $response = str_replace('&lt;NegotiatedRates&gt;', '<strong>&lt;NegotiatedRates&gt;', $response);
            $response = str_replace('&lt;/NegotiatedRates&gt;', '&lt;/NegotiatedRates&gt;</strong>', $response);
            print_r($response);
            echo '</pre>';
        }
        if (self::WIDGETIZED_DEBUG>1) 
        {
            die;
        }
    } 
}