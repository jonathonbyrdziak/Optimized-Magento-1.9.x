<?php

/**
 * Description of Creditcards
 *
 * @author Jonathon
 */
class Widgetized_Level3_Block_Account_Card_Edit extends Mage_Core_Block_Template {
    
    /**
     * 
     * @return type
     */
    public function getCard() {
        return Mage::registry('current_card');
    }
    
    /**
     * 
     * @param type $property
     */
    public function getInfoData( $property ) {
        $cardModel = $this->getCard();
        if (!$cardModel) return '';
        
        $data = $this->getCard()->getData();
        return $data[$property];
    }
    
    /**
     * Retrieve payment method model
     *
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function getMethod()
    {
        $helper = Mage::helper('payment');
        $methods = $helper->getStoreMethods();
        
        $methodArray = array();
        foreach ($methods as $method) {
            $methodArray[$method->getCode()] = $method;
        }
        
        return $methodArray['revolution'];
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = $this->_getConfig()->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code=>$name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }
        return $types;
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] =  $this->__('Month');
            $months = array_merge($months, $this->_getConfig()->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getConfig()->getYears();
            $years = array(0=>$this->__('Year'))+$years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        return parent::_toHtml();
    }
}
