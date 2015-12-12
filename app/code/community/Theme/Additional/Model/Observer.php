<?php

/**
 * Description of Observer
 *
 * @author Jonathon
 */
class Theme_Additional_Model_Observer extends Mage_Core_Model_Abstract {
    // Configuration path for our custom cms homepage
    const XML_PATH_FOR_CONFIG_TO_CUSTOM_HOMEPAGE = 'home_logged_in';
    /**
     * Substitutes homepage during by replacing
     * configuration value for the current store
     *
     */
    public function substituteHomePage()
    {
//        Mage_Cms_Helper_Page::XML_PATH_HOME_PAGE = web/default/cms_home_page
//        Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_HOME_PAGE) = home
        
        if (Mage::helper('customer')->isLoggedIn()) {
            // Substitute only if page is defined
            Mage::app()->getStore()->setConfig(
                Mage_Cms_Helper_Page::XML_PATH_HOME_PAGE,
                self::XML_PATH_FOR_CONFIG_TO_CUSTOM_HOMEPAGE
            );
        }
    }
}
