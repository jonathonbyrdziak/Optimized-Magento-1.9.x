<?php
/** 
 * 
 * - Ability to check order status
 * 
 In Enterprise we now have a new file for the B4School that will contain the 
 * package tracking information. One or more per order, one for each tracking 
 * number. 
 * 
 * 

 File Name........ Z1OSOHXRF                                                    
   Library........   ID2662AFB4                                                 
 Format Descr.....                                                              
 Format Name...... Z1SOHXRF                                                     
 File Type........ PF            Unique Keys - N                                

 Field Name FMT Start Lngth Dec Key Field Description                           
 HXTORN      S      1    12  00 K01 Temporary order number (web order#)                     
 HXORDT      A     13     2         Order type                                  
 HXORNO      S     15    12  00     Order number                                
 HXODAT      S     27     8  00     Order date                                  
 HXOREF      A     35    35         Customer facing order number reference  
 HXTREF      A     70    35         UPS TRACKING                                    
 HXSTMP1     A    105    26                                                     
 HXSTMP2     A    131    26    
 * 
 * 
 */
class Widgetized_Idpas400_Model_Erp_Upstracking 
    extends Widgetized_Idpas400_Model_Abstract {

    /**
     *
     * @var type 
     */
    protected $_tableName = 'Z1OSOHXRF';
    
    /**
     *
     * @var type 
     */
    protected $_id = 'HXTORN';

    /**
     *
     * @var type 
     */
    protected $_mapping = array(
        'HXTORN' => 'entity_id',
        'HXORDT' => 'type',
        'HXORNO' => 'order_number',
        'HXODAT' => 'date',
        'HXOREF' => 'increment_id',
        'HXTREF' => 'ups_tracking',
        'HXSTMP1'=> 'timestamp'
    );
    
    protected $_className = 'idpas400/erp_upstracking';
    
    /**
     * 
     * @return type
     */
    public function getCollection() {
        $sql = "SELECT * FROM $this->_tableName WHERE HXTORN = '$this->_id'";
        $records = Mage::getSingleton('idpas400/db')->fetch_array($sql);

        foreach ($records as $key => $record) {
            $instance = Mage::getModel($this->_className)->load(trim($record['ID']));
            $records[$key] = $instance;
        }
        return $records;
    }
    
    /**
     * 
     */
    public function getTrackingNumber() {
        return trim($this->getData('ups_tracking'));
    }
    
    /**
     * 
     */
    public function getTrackingNumbers( $_id ) {
        $sql = "SELECT HXTREF FROM $this->_tableName WHERE HXTORN = '$_id'";
        $records = Mage::getSingleton('idpas400/db')->fetch_array($sql);
        
        $numbers = array();
        foreach ($records as $key => $record) {
            $instance = trim($record['HXTREF']);
            if (!$instance) continue;
            $numbers[] = $instance;
        }
        return $numbers;
    }
}
