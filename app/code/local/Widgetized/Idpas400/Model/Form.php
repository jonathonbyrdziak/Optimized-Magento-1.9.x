<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Mage/Customer/Model/Form.php';
/**
 * Description of Form
 *
 * @author Jonathon
 */
class Widgetized_Idpas400_Model_Form extends Mage_Customer_Model_Form {
    /**
     * 
     * @return type
     */
    public function getAttributes() {
        $attributes = parent::getAttributes();
        
        if (Mage::app()->getRequest()->getRouteName()=='adminhtml') {
            if (Mage::app()->getRequest()->isPost()) {
                $data = Mage::app()->getRequest()->getParam('tabCustomerattribute');
                $customerTypeValue = $data['select_customer_type'];
                $customerTypeText = Mage::helper('idpas400')->getAttributeOptionText($customerTypeValue);

                $attributes = Mage::helper('idpas400')->filterAttributesArray( $attributes, $customerTypeText );
            }
            
        // Filter the attributes if this is a post on the front controller
        } elseif (Mage::app()->getRequest()->isPost()) {
            $customerTypeValue = Mage::app()->getRequest()->getParam('select_customer_type');
            $customerTypeText = Mage::helper('idpas400')->getAttributeOptionText($customerTypeValue);
        
            $attributes = Mage::helper('idpas400')->filterAttributesArray( $attributes, $customerTypeText );
            
        // if the user is logged in, then we're looking at their account page
//        } elseif (Mage::helper('customer')->isLoggedIn()) {
//            $_attr = array();
//            foreach ($attributes as $code => $attribute) {
//                if ($code == 'select_customer_type') {
//                    $attribute->setData('is_visible',0);
//                    $attribute->setData('is_required',0);
//                    continue;
//                }
//                $_attr[$code] = $attribute;
//            }
//            $attributes = $_attr;
        }
        
        return $attributes;
    }
}
