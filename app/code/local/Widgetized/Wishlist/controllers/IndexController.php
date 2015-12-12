<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'Mage/Wishlist/controllers/IndexController.php';
/**
 * Wishlist front controller
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Widgetized_Wishlist_IndexController extends Mage_Wishlist_IndexController
{
    /**
     * 
     * @param type $response
     */
    public function _echoJson( $response ) {
        header('Content-type: application/json');
        echo json_encode($response);
        die;
    }

    /**
     * Extend preDispatch
     *
     * @return Mage_Core_Controller_Front_Action|void
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->_skipAuthentication && !Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
            if (!Mage::getSingleton('customer/session')->getBeforeWishlistUrl()) {
                Mage::getSingleton('customer/session')->setBeforeWishlistUrl($this->_getRefererUrl());
            }
            Mage::getSingleton('customer/session')->setBeforeWishlistRequest($this->getRequest()->getParams());
        }
        if (!Mage::getStoreConfigFlag('wishlist/general/active')) {
            $this->norouteAction();
            return;
        }
    }

    /**
     * Adding new item
     *
     * @return Mage_Core_Controller_Varien_Action|void
     */
    public function addAction()
    {
        $response = array();
        if (!$this->_validateFormKey()) {
            $response['error'] = 'Your session may have expired.';
            $response['redirect'] = Mage::getUrl('customer/account/login');
            return $this->_echoJson($response);
        }
        
        $this->_addItemToWishList();
    }

    /**
     * Add the item to wish list
     *
     * @return Mage_Core_Controller_Varien_Action|void
     */
    protected function _addItemToWishList()
    {
        $response = array();
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            $response['error'] = 'No wishlist available';
            return $this->_echoJson($response);
        }

        $session = Mage::getSingleton('customer/session');

        $productId = (int)$this->getRequest()->getParam('product');
        if (!$productId) {
            $response['error'] = 'No product available';
            return $this->_echoJson($response);
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $response['error'] = $this->__('Cannot specify product.');
            $response['redirect'] = Mage::getUrl();
            return $this->_echoJson($response);
        }

        try {
            $requestParams = $this->getRequest()->getParams();
            if ($session->getBeforeWishlistRequest()) {
                $requestParams = $session->getBeforeWishlistRequest();
                $session->unsBeforeWishlistRequest();
            }
            $buyRequest = new Varien_Object($requestParams);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                Mage::throwException($result);
            }
            $wishlist->save();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist' => $wishlist,
                    'product' => $product,
                    'item' => $result
                )
            );

            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_getRefererUrl();
            }

            /**
             *  Set referer to avoid referring to the compare popup window
             */
            $session->setAddActionReferer($referer);

            Mage::helper('wishlist')->calculate();

            $message = $this->__('%1$s has been added to your wishlist. Click <a href="%2$s">here</a> to continue shopping.',
                $product->getName(), Mage::helper('core')->escapeUrl($referer));
            $response['message'] = $message;
            
        } catch (Mage_Core_Exception $e) {
            $response['error'] = $this->__('An error occurred while adding item to wishlist: %s', $e->getMessage());
        }
        catch (Exception $e) {
            $response['error'] = $this->__('An error occurred while adding item to wishlist.');
        }

        $response['status'] = 'added';
        return $this->_echoJson($response);
    }

    /**
     * Remove item
     */
    public function removeAction()
    {
        $response = array();
        $id = (int) $this->getRequest()->getParam('product');
        if ($id)
        {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer, true); 

            $wishlist = Mage::getModel('wishlist/item')->getCollection()
                    ->addFieldToFilter('wishlist_id', $wishlist->getId())
                    ->addFieldToFilter('product_id', $id);
            $item = $wishlist->getFirstItem();

            if (!$item->getId()) {
                $response['error'] = 'No product available';
                return $this->_echoJson($response);
            }
            if (!$wishlist) {
                $response['error'] = 'No wishlist available';
                return $this->_echoJson($response);
            }
            
        } else {
            return parent::removeAction();
        }
        try {
            $item->delete();
            $wishlist->save();
        } catch (Mage_Core_Exception $e) {
            $response['error'] = $this->__('An error occurred while deleting the item from wishlist: %s', $e->getMessage());
            return $this->_echoJson($response);
        } catch (Exception $e) {
            $response['error'] = $this->__('An error occurred while deleting the item from wishlist.');
            return $this->_echoJson($response);
        }
        
        Mage::helper('wishlist')->calculate();

        $response['status'] = 'removed';
        $response['message'] = $this->__('Product was been removed from your wishlist.');
        return $this->_echoJson($response);
    }

}
