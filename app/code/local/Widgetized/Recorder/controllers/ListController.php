<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Widgetized_Recorder_ListController extends Mage_Adminhtml_Controller_Action {

    /**
     * 
     * @return type
     */
    protected function _prepareLayout() {
        return parent::_prepareLayout();
    }

    /**
     * 
     */
    public function indexAction() {
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            $this->_redirect('/');
            return;
        }
        
        $this->loadLayout();

        $block = $this->getLayout()
                ->createBlock('recorder/adminhtml_orders', 'recorder-grid');
        $this->_addContent($block);

        
        $block = $this->getLayout()
                ->createBlock('core/text', 'recorder-introduction')
                ->setText($this->getPopup());
        $this->_addContent($block);
        
        $this->renderLayout();
    }
    
    /**
     * 
     */
    public function resetAction() {
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            $this->_redirect('*/*/index');
            return;
        }
        
        $ids = $this->getRequest()->getParam('recurring_ids');
        if(!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select an order'));
        } else {
            foreach ($ids as $id) {
                try {
                    $order = Mage::getModel('recorder/order')->load($id);
                    $order->reset();
                    
                } catch (Exception $e) {
                    $order->addError($e->getMessage());
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
            
            Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                            'Total of %d orders(s) were successfully reset', count($ids)
                    )
            );
        }
        $this->_redirect('*/*/index');
    }
    
    /**
     * 
     */
    public function massDeleteAction() {
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            $this->_redirect('*/*/index');
            return;
        }
        
        $ids = $this->getRequest()->getParam('recurring_ids');
        if(!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select an order'));
        } else {
            foreach ($ids as $id) {
                try {
                    $order = Mage::getModel('recorder/order')->load($id);
                    $order->delete();
                    
                } catch (Exception $e) {
                    $order->addError($e->getMessage());
                    $order->save();
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
                
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d orders(s) were successfully deleted', count($ids)
                    )
                );
            }
        }
        $this->_redirect('*/*/index');
    }
   
    /**
     * 
     */
    public function massStatusAction() {
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            $this->_redirect('*/*/index');
            return;
        }
        
        $ids = $this->getRequest()->getParam('recurring_ids');
        if(!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select an order'));
        } else {
            foreach ($ids as $id) {
                try {
                    $orders = Mage::getSingleton('recorder/order')
                            ->load($id)
                            ->setEnabled($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                    
                } catch (Exception $e) {
                    $order->addError($e->getMessage());
                    $order->save();
                    $this->_getSession()->addError($e->getMessage());
                }
            }
            
            $this->_getSession()->addSuccess(
                    $this->__('Total of %d orders(s) were successfully updated', count($ids))
            );
        }
        $this->_redirect('*/*/index');
    }
    
    /**
     * 
     * @return type
     */
    protected function getPopup() {
        ob_start();
        
        ?>
        <script type="text/javascript">
            function openOrderProcessing() {
                var dialogWindow = Dialog.info(null, {
                    closable:true,
                    resizable:false,
                    draggable:true,
                    className:'mac_os_x_dialog',
                    windowClassName:'popup-window mac_os_x_dialog',
                    title:'Processing Recurring Orders',
                    top:50,
                    width:800,
                    height:500,
                    zIndex:1000,
                    recenterAuto:false,
                    hideEffect:Element.hide,
                    showEffect:Element.show,
                    id:'browser_window',
                    url:'<?php echo $this->getUrl('externaldb/test/recurringorders') ?>?',
                    onClose:function (param, el) {
                        
                    }
                });
            }
            function closePopup() {
                Windows.close('browser_window');
            }
            function resetOrderProcessing() {
                var _url = BASE_URL.replace('recurring/index/index/', 'recurring/list/reset/');
                new Ajax.Request(_url, {
                    method: 'post',
                    parameters: Form.serialize('orders_grid', true),
                    onSuccess: function(response) {
//                        console.log('response', response);
                        
                        
                        
                    },
                    onComplete: function(response) {
//                        console.log('onComplete', response);
                    }
                });
                console.log('last', _url);
            }
        </script>
        <link rel="stylesheet" type="text/css" href="<?php echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS); ?>prototype/windows/themes/default.css" media="all" />
        <link rel="stylesheet" type="text/css" href="<?php echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS); ?>prototype/windows/themes/mac_os_x_dialog.css" media="all" />
        <style>
            .title_window {
                padding: 20px;
                font-size: 20px;
            }
            #browser_window {
                background: #fff;
            }
            #browser_window_close:before {
                content: '\03C7';
            }
        </style>
        <?php
        
        return ob_get_clean();
    }
}
