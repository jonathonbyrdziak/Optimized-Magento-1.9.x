<?php

class Widgetized_Level3_Block_Revolution_Cc 
    extends Mage_Paygate_Block_Authorizenet_Form_Cc
{
    
    /**
     * Retreive payment method form html
     *
     * @return string
     */
    public function getMethodFormBlock()
    {
        return $this->getLayout()->createBlock('level3/form_cc')
            ->setMethod($this->getMethod());
    }
    
    
    public function hasVerification() {
        return true;
    }

}

