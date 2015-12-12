<?php
class Theme_Additional_Block_Customer_Account_Navigation extends Mage_Customer_Block_Account_Navigation
{
    public function removeLinkByName($name)
    {
        unset($this->_links[$name]);
    }
}