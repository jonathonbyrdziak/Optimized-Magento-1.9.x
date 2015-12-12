<?php


class Widgetized_Level3_Block_Form_Cc extends Mage_Payment_Block_Form_Cc
{
    
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paytrace/form/cc.phtml');
    }
}