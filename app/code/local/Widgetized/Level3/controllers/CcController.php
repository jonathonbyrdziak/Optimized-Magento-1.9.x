<?php

/**
 * Description of Creditcards
 *
 * @author Jonathon
 */
class Widgetized_Level3_CcController extends Mage_Core_Controller_Front_Action {
    
    /**
     * We only want logged in users working on these cards
     */
    protected function isAuthorized() {
        if (!Mage::helper('customer')->isLoggedIn()) {
            header('Location: '. Mage::getUrl() );
            die;
        }
    }
    
    /**
     * 
     */
    protected function isAuthorizedCard() {
        $cardModel = Mage::registry('current_card');
        
        $customerId = Mage::helper('customer')->getCustomer()->getId();
        if ($cardModel->getCustomerId() == $customerId) 
            return true;
        return false;
    }
    
    /**
     * List of credit cards
     */
    public function indexAction() {
        $this->isAuthorized();
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * Prepares to edit a card or create a new one
     * 
     * 
     * UPDATE THE PAYTRACE CUSTOMER
     * http://help.paytrace.com/api-update-customer-profile
     * 
     */
    public function editAction() {
        $this->isAuthorized();
        
        $cardModel = Mage::getModel('level3/card');
        
        // loading an existing card
        $card_id = $this->getRequest()->getParam('card_id', false);
        if ($card_id) {
            $cardModel->load($card_id);
        }
        
        Mage::register('current_card', $cardModel);
        
        // Make sure this is the right user
        $this->isAuthorizedCard();
        
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * Deletes a credit card
     */
    public function deleteAction() {
        $this->isAuthorized();
        
        $card_id = $this->getRequest()->getParam('card_id', false);
        $cardModel = Mage::getModel('level3/card')->load($card_id);
        $dleted = $cardModel->delete();
        
        // UPDATE A NEW CUSTOMER at paytrace
        Mage::helper('level3/paytrace')->paytraceCustomer( 
                Mage::getSingleton('customer/session')->getCustomer()
                );
        
        if ($dleted) {
            $message = $this->__("Your card was deleted.");
            Mage::getSingleton('core/session')->addSuccess($message);
        } else {
            $message = $this->__("Your card could not be deleted.");
            Mage::getSingleton('core/session')->addError($message);
        }
        $this->_redirectUrl( Mage::getUrl()."level3/cc/" );
    }
    
    /**
     * Saves a credit card
     * 
     * 
     * CREATE A NEW CUSTOMER 
     */
    public function saveAction() {
        $this->isAuthorized();
        
        $cardModel = Mage::helper('level3')->savePostedCard();
        
        if (!$cardModel->getToken()) {
            $message = $this->__("Your card was saved.");
            Mage::getSingleton('core/session')->addSuccess($message);
        } else {
            Mage::getSingleton('core/session')->addError($cardModel->getToken());
        }
        
        if (!trim($cardModel->getToken())) {
            $this->_redirectUrl( Mage::getUrl()."level3/cc/" );
            return;
        }
        $this->_redirectUrl( Mage::getUrl()."level3/cc/edit/?card_id=".$cardModel->getId() );
    }
}
