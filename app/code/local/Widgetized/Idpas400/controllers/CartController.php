<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CartController
 *
 * @author Jonathon
 */
require_once 'Mage/Checkout/controllers/CartController.php';
class Widgetized_Idpas400_CartController extends Mage_Checkout_CartController {
    /**
     * FOR AVATAX
     * Initialize shipping information
     */
    public function estimatePostAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setPostType('estimate');
        parent::estimatePostAction();
    }

    /**
     * 
     */
    public function couponPostAction() {
        // applies a discount if it's there
        Mage::helper('idpas400')->apply_coupon($this->_getQuote(),$this->getRequest());
        
        parent::couponPostAction();
    }

    /**
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction()
    {
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            if (is_string($item)) {
                Mage::throwException($item);
            }
            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $params['success'] = $this->__('%s was updated in your shopping cart.', Mage::helper('core')->escapeHtml($item->getProduct()->getName()));
                    $params['redirect'] = Mage::helper('checkout/cart')->getCartUrl();
                }
                $this->_echoJson($params);
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $params['error'][] = $e->getMessage();
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $params['error'][] = $message;
                }
            }

            $params['redirect'] = $this->_getSession()->getRedirectUrl(true);
            if (!$params['redirect']) {
                $params['redirect'] = Mage::helper('checkout/cart')->getCartUrl();
            }
        } catch (Exception $e) {
            $params['error'][] = $this->__('Cannot update the item.');
        }
        $this->_echoJson($params);
    }
    
    /**
     * 
     * @param type $response
     */
    public function _echoJson( $response ) {
        echo json_encode($response);
        die;
    }
}
