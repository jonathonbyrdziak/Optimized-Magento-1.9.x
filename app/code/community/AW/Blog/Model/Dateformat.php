<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento professional edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Blog
 * @version    1.3.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Blog_Model_Dateformat
{
    const FORMAT_TYPE_FULL   = 'full';
    const FORMAT_TYPE_LONG   = 'long';
    const FORMAT_TYPE_MEDIUM = 'medium';
    const FORMAT_TYPE_SHORT  = 'short';

    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options[] = array(
                'value' => self::FORMAT_TYPE_FULL,
                'label' => Mage::helper('blog')->__('Full')
            );
            $this->_options[] = array(
                'value' => self::FORMAT_TYPE_LONG,
                'label' => Mage::helper('blog')->__('Long')
            );
            $this->_options[] = array(
                'value' => self::FORMAT_TYPE_MEDIUM,
                'label' => Mage::helper('blog')->__('Medium')
            );
            $this->_options[] = array(
                'value' => self::FORMAT_TYPE_SHORT,
                'label' => Mage::helper('blog')->__('Short')
            );
        }
        return $this->_options;
    }
}