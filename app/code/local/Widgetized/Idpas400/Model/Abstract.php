<?php

/**
 * 
 */
class Widgetized_Idpas400_Model_Abstract extends Mage_Core_Model_Abstract {
    
    /**
     * 
     */
    public function __construct() {
        
    }
    
    public function getId(){
        return $this->getData('entity_id');
    }
    
    /**
     * 
     * @return type
     */
    public function getCollection() {
        $sql = "SELECT $this->_id as ID  FROM $this->_tableName";
        $records = Mage::getSingleton('idpas400/db')->fetch_array($sql);

        foreach ($records as $key => $record) {
            $instance = Mage::getModel($this->_className)->load(trim($record['ID']));
            $records[$key] = $instance;
        }
        return $records;
    }
    
    /**
     * 
     * @param type $id
     */
    public function load( $id ) {
        //loading primary data
        $sql = "SELECT *"
                . " FROM $this->_tableName"
                . " WHERE $this->_id = '$id'";
        
        $data = Mage::getSingleton('idpas400/db')->fetch_row($sql);
        $this->_origData = $data;
        $this->bind($data);
        
        // loading descriptions
        return $this;
    }
    
    /**
     * 
     * @param type $data
     */
    public function bind($data) {
        if (is_array($data)) {
            if (!empty($this->_mapping)) {
                $newdata = array();
                foreach ($this->_mapping as $from => $to) {
                    if (!array_key_exists($from, $data) || is_null($data[$from])) 
                            continue;
                    $newdata[$to] = trim($data[$from]);
                }
            }
            $newdata = array_map('trim', $newdata);
            return parent::setData($newdata);
        }
    }
    
    /**
     * 
     */
    protected function _getResource(){}
}
