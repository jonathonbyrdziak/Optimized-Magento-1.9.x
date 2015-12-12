<?php
/**
 * Normal order status is determined by file SRBSOH, field OHORDS. 
 * This file is keyed by the enterprise order number. The new file Z1OSOHXRF 
 * will contain the web sites internal order number in HXTORN. This web order 
 * number can be used to select Z1OSOHXRF and join to SRBSOH where 
 * HXORNO=OHORNO. The order status is determined by the content of OHORDS in 
 * SRBSOH. 
 * 
OHORDS values:
 * 
10=Order has been entered into the system
18=The order has been entered into the system with a delivery date later than today's date, and will be processed in the future.
20=The order conformation has been produced
30=Pick instructions have been produced
40=The order contains at least one item ready to ship
45=The transport note has been produced, shipping document produced.
60=Complete
 * 

 File Name........ Z1OSOHXRF                                                    
   Library........   ID2662AFB4                                                 
 Format Descr.....                                                              
 Format Name...... Z1SOHXRF                                                     
 File Type........ PF            Unique Keys - N     
 * 
 * OHORNO = Enterprise order number..  
 * OHCOPE = Your Temp # (#481)..  
 * OHCORN = Customer facing# 100000419
 * 
 */
class Widgetized_Idpas400_Model_Erp_Orders extends Widgetized_Idpas400_Model_Abstract {
    
    /**
     *
     * @var type 
     */
    protected $_tableName = 'SRBSOH';
    
    /**
     *
     * @var type 
     */
    protected $_id = 'OHORNO';

    /**
     *
     * @var type 
     */
    protected $_mapping = array(
        'OHORNO' => 'entity_id',
        'OHCOPE' => 'temp_id',
        'OHTYPE' => 'type',
        'OHORDS' => 'status',
        'OHCORN' => 'order_number', // @todo b4requirement
//        'ERP_COLUMN_FOR_COMMENT' => 'comment',
    );
    
    protected $_className = 'idpas400/erp_orders';
    
    /**
     * 
     * @return type
     */
    public function getTrackingNumbers() {
        return  Mage::getModel('idpas400/erp_upstracking')->getTrackingNumbers($this->getId());
    }
    
    /**
     * 
     * REFERENCE:
        10 => 'Order has been entered into the system',
        18 => 'The order has been entered into the system with a delivery date later than today\'s date, and will be processed in the future.',
        20 => 'The order conformation has been produced',
        30 => 'Pick instructions have been produced',
        40 => 'The order contains at least one item ready to ship',
        45 => 'The transport note has been produced, shipping document produced.',
        60 => 'Complete'
     * 
     * @return type
     */
    public function getStatus() {
        switch ($this->getData('status')) {
            case 10: return Mage_Sales_Model_Order::STATE_PROCESSING;
            case 18: return Mage_Sales_Model_Order::STATE_PROCESSING;
            case 20: return Mage_Sales_Model_Order::STATE_PROCESSING;
            case 30: return Mage_Sales_Model_Order::STATE_PROCESSING;
            case 40: return Mage_Sales_Model_Order::STATE_PROCESSING;
            case 45: return Mage_Sales_Model_Order::STATE_COMPLETE;
            case 60: return Mage_Sales_Model_Order::STATE_COMPLETE;
            default: return false;
        }
    }
    
    /**
     * 
     * @param type $temp_id
     * @return \Widgetized_Idpas400_Model_Erp_Orders
     */
    public function loadByTempId( $temp_id ) {
        //loading primary data
        $sql = "SELECT *"
                . " FROM $this->_tableName"
                . " WHERE `OHCOPE` LIKE '$id%'";
        
        $data = Mage::getSingleton('idpas400/db')->fetch_row($sql);
        $this->_origData = $data;
        $this->bind($data);
        
        // loading descriptions
        return $this;
    }
    
    /**
     * 
     * @param type $id
     * @return \Widgetized_Idpas400_Model_Erp_Orders
     */
    public function load( $id ) {
        //loading primary data
        $sql = "SELECT *"
                . " FROM $this->_tableName"
                . " WHERE $this->_id LIKE '$id%'";
        
        $data = Mage::getSingleton('idpas400/db')->fetch_row($sql);
        $this->_origData = $data;
        $this->bind($data);
        
        // loading descriptions
        return $this;
    }
}
