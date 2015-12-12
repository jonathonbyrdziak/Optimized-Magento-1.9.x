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


class AW_Blog_Helper_Cat extends Mage_Core_Helper_Abstract
{
    /**
     * Renders CMS page
     * Call from controller action
     *
     * @param Mage_Core_Controller_Front_Action $action
     * @param integer                           $identifier
     *
     * @return bool
     */
    public function renderPage(Mage_Core_Controller_Front_Action $action, $identifier = null)
    {
        if (!$catId = Mage::getSingleton('blog/cat')->load($identifier)->getCatId()) {
            return false;
        }

        $pageTitle = Mage::getSingleton('blog/cat')->load($identifier)->getTitle();
        $blogTitle = Mage::getStoreConfig('blog/blog/title') . " - " . $pageTitle;

        $action->loadLayout();
        if ($storage = Mage::getSingleton('customer/session')) {
            $action->getLayout()->getMessagesBlock()->addMessages($storage->getMessages(true));
        }
        $action->getLayout()->getBlock('head')->setTitle($blogTitle);

        $action->getLayout()->getBlock('root')->setTemplate(Mage::getStoreConfig('blog/blog/layout'));
        $action->renderLayout();

        return true;
    }
}