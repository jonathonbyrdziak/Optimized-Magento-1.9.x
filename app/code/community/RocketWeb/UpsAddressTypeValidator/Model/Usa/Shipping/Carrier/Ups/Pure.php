<?php
if (Mage::getStoreConfig('shipping/shipusa/active')){
    class RocketWeb_UpsAddressTypeValidator_Model_Usa_Shipping_Carrier_Ups_Pure extends Webshopapps_Shipusa_Model_Shipping_Carrier_Ups {}
}
else {
    class RocketWeb_UpsAddressTypeValidator_Model_Usa_Shipping_Carrier_Ups_Pure extends Mage_Usa_Model_Shipping_Carrier_Ups {}
}