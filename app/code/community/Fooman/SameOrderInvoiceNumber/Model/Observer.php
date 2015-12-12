<?php

/**
 * Fooman Order = Invoice Number
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Fooman
 * @package    SameOrderInvoiceNumber
 * @copyright  Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Fooman_SameOrderInvoiceNumber_Model_Observer
{

    public function sales_order_invoice_save_before ($observer)
    {

        $invoice = $observer->getInvoice();
        if (!$invoice->getId()) {
            $order = $invoice->getOrder();
            $storeId = $order->getStore()->getStoreId();
            $prefix = Mage::getStoreConfig('sameorderinvoicenumber/settings/invoiceprefix',
                            $storeId);
                $newInvoiceNr = 0;
                $currentPostfix = 0;
                while (!$newInvoiceNr) {
                    if ($currentPostfix) {
                        $newInvoiceNr = $prefix . $order->getIncrementId() . '-' . $currentPostfix;
                    } else {
                        $newInvoiceNr = $prefix . $order->getIncrementId();
                    }
                    $collection = Mage::getModel('sales/order_invoice')->getCollection()->addFieldToFilter('increment_id',
                            $newInvoiceNr);
                    if ($collection->getAllIds()) {
                        //number already exists
                        $newInvoiceNr = 0;
                        $currentPostfix++;
                    } else {
                        $invoice->setIncrementId($newInvoiceNr);
                    }
                }
        }
    }

    public function sales_order_shipment_save_before ($observer)
    {
        $shipment = $observer->getShipment();
        if (!$shipment->getId()) {
            $order = $shipment->getOrder();
            $storeId = $order->getStore()->getStoreId();
            $prefix = Mage::getStoreConfig('sameorderinvoicenumber/settings/shipmentprefix',
                            $storeId);
                $newShipmentNr = 0;
                $currentPostfix = 0;
                while (!$newShipmentNr) {
                    if ($currentPostfix) {
                        $newShipmentNr = $prefix . $order->getIncrementId() . '-' . $currentPostfix;
                    } else {
                        $newShipmentNr = $prefix . $order->getIncrementId();
                    }
                    $collection = Mage::getModel('sales/order_shipment')->getCollection()->addFieldToFilter('increment_id',
                            $newShipmentNr);
                    if ($collection->getAllIds()) {
                        //number already exists
                        $newShipmentNr = 0;
                        $currentPostfix++;
                    } else {
                        $shipment->setIncrementId($newShipmentNr);
                    }
                }
        }
    }

    public function sales_order_creditmemo_save_before ($observer)
    {
        $creditmemo = $observer->getCreditmemo();
        if (!$creditmemo->getId()) {
            $order = $creditmemo->getOrder();
            $storeId = $order->getStore()->getStoreId();
            $prefix = Mage::getStoreConfig('sameorderinvoicenumber/settings/creditmemoprefix',
                            $storeId);
                $newCreditmemoNr = 0;
                $currentPostfix = 0;
                while (!$newCreditmemoNr) {
                    if ($currentPostfix) {
                        $newCreditmemoNr = $prefix . $order->getIncrementId() . '-' . $currentPostfix;
                    } else {
                        $newCreditmemoNr = $prefix . $order->getIncrementId();
                    }
                    $collection = Mage::getModel('sales/order_creditmemo')->getCollection()->addFieldToFilter('increment_id',
                            $newCreditmemoNr);
                    if ($collection->getAllIds()) {
                        //number already exists
                        $newCreditmemoNr = 0;
                        $currentPostfix++;
                    } else {
                        $creditmemo->setIncrementId($newCreditmemoNr);
                    }
                }
        }
    }

}
