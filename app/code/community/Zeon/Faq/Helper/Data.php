<?php

/**
 * zeonsolutions inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.zeonsolutions.com/shop/license-community.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * This package designed for Magento COMMUNITY edition
 * =================================================================
 * zeonsolutions does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * zeonsolutions does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   Zeon
 * @package    Zeon_Faq
 * @version    0.0.1
 * @copyright  @copyright Copyright (c) 2013 zeonsolutions.Inc. (http://www.zeonsolutions.com)
 * @license    http://www.zeonsolutions.com/shop/license-community.txt
 */
class Zeon_Faq_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_DEFAULT_META_TITLE = 'zeon_faq/frontend/meta_title';
    const XML_PATH_DEFAULT_META_KEYWORDS = 'zeon_faq/frontend/meta_keywords';
    const XML_PATH_DEFAULT_META_DESCRIPTION = 'zeon_faq/frontend/meta_description';
    const XML_PATH_DEFAULT_IS_DISPLAY_MFAQ = 'zeon_faq/frontend/is_display_mfaq';

    public function getIsDisplayMfaq()
    {
        return Mage::getStoreConfig(self::XML_PATH_DEFAULT_IS_DISPLAY_MFAQ);
    }
    /**
     * Retrieve default title for faq
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return Mage::getStoreConfig(self::XML_PATH_DEFAULT_META_TITLE);
    }

    /**
     * Retrieve default meta keywords for faq
     *
     * @return string
     */
    public function getDefaultMetaKeywords()
    {
        return Mage::getStoreConfig(self::XML_PATH_DEFAULT_META_KEYWORDS);
    }

    /**
     * Retrieve default meta description for faq
     *
     * @return string
     */
    public function getDefaultMetaDescription()
    {
        return Mage::getStoreConfig(self::XML_PATH_DEFAULT_META_DESCRIPTION);
    }

    /**
     * Retrieve search query param
     *
     * @return string
     */
    public function getQueryParam()
    {
        return $this->_getRequest()->getParam('faqsearch');
    }
    
    /**
     * Retrieve Template processor for Block Content
     *
     * @return Varien_Filter_Template
     */
    public function getBlockTemplateProcessor()
    {
        return Mage::getModel('zeon_faq/template_filter');
    }
}