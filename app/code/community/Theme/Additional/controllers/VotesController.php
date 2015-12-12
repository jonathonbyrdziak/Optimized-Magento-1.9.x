<?php
class Theme_Additional_VotesController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        if (!$_GET['ajax'] == 1) {
            $this->_forward('noRoute');
        } else {
            $id = (int)$this->getRequest()->getParam('product');

            if ($id) {
                $_product = Mage::getModel('catalog/product')->load($id);

                if ($_product) {
                    $_cookie = Mage::getSingleton('core/cookie');
                    $_productIds = unserialize($_cookie->get('vote'));

                    if (!in_array($_product->getId(), $_productIds)) {
                        $_votes = (int)$_product->getVotes();

                        if (!$_votes) {
                            $_votes = 0;
                        }

                        $_votes = ++$_votes;
                        $_product->setData('votes', $_votes);
                        $_product->save();

                        $_productIds[] = $_product->getId();
                        $_value = serialize($_productIds);
                        $_cookie->set('vote', $_value, time() + 86400, '/');
                        echo $this->__('%s Votes!', $_votes);
                    }
                }
            }
        }
    }
}
