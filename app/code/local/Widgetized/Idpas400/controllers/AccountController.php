<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Mage/Customer/controllers/AccountController.php';

/**
 * Description of AccountController
 *
 * @author Jonathon
 */
class Widgetized_Idpas400_AccountController extends Mage_Customer_AccountController {
    
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
     * 
     * @param Varient_Event_Observer $observer
     */
    public function _loginPostRedirect() {
        $session = $this->_getSession();
        
        $checkoutreferrer = Mage::app()->getRequest()->getParam('checkoutreferrer',false);
        if ($session->isLoggedIn() && $checkoutreferrer) {
            $session->setBeforeAuthUrl( Mage::getUrl('checkout/onepage') );
        } elseif ($session->isLoggedIn()) {
            $session->setBeforeAuthUrl( Mage::getBaseUrl() );
        } else {
            $session->setBeforeAuthUrl( $this->_getHelper('customer')->getLoginUrl() );
        }
        
        $this->_redirectUrl($session->getBeforeAuthUrl(true));
    }

    /**
     * Success Registration
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Customer_AccountController
     */
    protected function _successProcessRegistration(Mage_Customer_Model_Customer $customer)
    {
        $session = $this->_getSession();
        if ($customer->isConfirmationRequired()) {
            /** @var $app Mage_Core_Model_App */
            $app = $this->_getApp();
            /** @var $store  Mage_Core_Model_Store*/
            $store = $app->getStore();
            $customer->sendNewAccountEmail(
                'confirmation',
                $session->getBeforeAuthUrl(),
                $store->getId()
            );
            $customerHelper = $this->_getHelper('customer');
            $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
                $customerHelper->getEmailConfirmationUrl($customer->getEmail())));
        } else {
            $session->setCustomerAsLoggedIn($customer);
            $this->_welcomeCustomer($customer);
        }
        
        $url = Mage::getUrl();
        $this->_redirectSuccess($url);
        return $this;
    }

    /**
     * Create customer account action
     */
    public function createAccountPostAction()
    {
        ob_start();
        $response = array();
    
        /** @var $session Mage_Customer_Model_Session */
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $response['redirect'] = Mage::getUrl();
            $this->_echoJson($response);
        }
        $session->setEscapeMessages(true);
        $customer = $this->_getCustomer();

        try {
            $errors = $this->_getCustomerErrors($customer);

            if (empty($errors)) {
                $customer->save();
                $this->_dispatchRegisterSuccess($customer);
                $this->_successProcessRegistrationJson($customer);
                return;
            } else {
                $response['error'] = $this->__('Missing fields');
            }
        } catch (Mage_Core_Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost());
            if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                $url = $this->_getUrl('customer/account/forgotpassword');
                $response['error'] = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                $session->setEscapeMessages(false);
            } else {
                $response['error'] = $e->getMessage();
            }
        } catch (Exception $e) {
            $response['error'] = $this->__('Cannot save the customer.');
        }
        ob_clean();
        $this->_echoJson($response);
    }

    /**
     * Success Registration
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Customer_AccountController
     */
    protected function _successProcessRegistrationJson(Mage_Customer_Model_Customer $customer)
    {
        $response = array();
        $session = $this->_getSession();
        if ($customer->isConfirmationRequired()) {
            /** @var $app Mage_Core_Model_App */
            $app = $this->_getApp();
            /** @var $store  Mage_Core_Model_Store*/
            $store = $app->getStore();
            $customer->sendNewAccountEmail(
                'confirmation',
                $session->getBeforeAuthUrl(),
                $store->getId()
            );
            $customerHelper = $this->_getHelper('customer');
            $response['error'] = $this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
                $customerHelper->getEmailConfirmationUrl($customer->getEmail()));
            $response['redirect'] = $this->_getUrl('*/*/index', array('_secure' => true));
        } else {
            $session->setCustomerAsLoggedIn($customer);
            $response['redirect'] = $this->_welcomeCustomer($customer);
        }
        $this->_echoJson($response);
    }
}
