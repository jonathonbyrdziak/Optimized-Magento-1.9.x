<?php

/**
 * Description of Creditcards
 *
 * @author Jonathon
 */
class Widgetized_Level3_Block_Account_Cards extends Mage_Core_Block_Template {
    
    /**
     * @TODO b4requirements get the list of saved credit cards
     * 
     * 
     * @return type
     */
    public function getCards() {
        return Mage::helper('level3')->getCards();
    }
}
