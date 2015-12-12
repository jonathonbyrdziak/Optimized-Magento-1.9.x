<?php
class Theme_Additional_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        if (!$_GET['ajax'] == 1) {
            $this->_forward('noRoute');
        } else {
            $this->loadLayout();
            $this->renderLayout();
        }
    }
}
