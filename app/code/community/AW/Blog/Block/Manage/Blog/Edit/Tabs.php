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


class AW_Blog_Block_Manage_Blog_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('blog_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('blog')->__('Post Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_section',
            array(
                 'label'   => Mage::helper('blog')->__('Post Information'),
                 'title'   => Mage::helper('blog')->__('Post Information'),
                 'content' => $this->getLayout()->createBlock('blog/manage_blog_edit_tab_form')->toHtml(),
            )
        );

        $this->addTab(
            'options_section',
            array(
                 'label'   => Mage::helper('blog')->__('Advanced Options'),
                 'title'   => Mage::helper('blog')->__('Advanced Options'),
                 'content' => $this->getLayout()->createBlock('blog/manage_blog_edit_tab_options')->toHtml(),
            )
        );

        return parent::_beforeToHtml();
    }
}