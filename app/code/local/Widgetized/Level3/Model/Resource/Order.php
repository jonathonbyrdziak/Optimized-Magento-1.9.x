<?php

class Widgetized_Level3_Model_Resource_Order 
    extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('level3/order', 'id');
    }

    /**
     * 
     * @param type $order_id
     * @param type $var
     */
    public function deleteOrder($order_id) {
        $table = $this->getMainTable();
        $where = $this->_getWriteAdapter()->quoteInto('`order_id` = ? ', $order_id);
        $sql = "DELETE FROM $table WHERE $where";
        
        $this->_getWriteAdapter()->delete($table, $where);
        $result = $this->_getWriteAdapter()->query($sql);
//        var_dump($sql);var_dump($result);
    }

    /**
     * 
     * @param type $order_id
     * @param type $var
     */
    public function deleteByOrder($order_id, $var) {
        $table = $this->getMainTable();
        $where = $this->_getWriteAdapter()->quoteInto('`order_id` = ? AND ', $order_id)
                . $this->_getWriteAdapter()->quoteInto('`key` = ? 	', $var);
        $sql = "DELETE FROM $table WHERE $where";
        
        $this->_getWriteAdapter()->delete($table, $where);
        $result = $this->_getWriteAdapter()->query($sql);
//        var_dump($sql);var_dump($result);
    }

    /**
     * 
     * @param type $order_id
     * @param type $var
     * @return type
     */
    public function getByOrder($order_id, $var = '') {
        $table = $this->getMainTable();
        $where = $this->_getReadAdapter()->quoteInto('order_id = ?', $order_id);
        if (!empty($var)) {
            $where .= $this->_getReadAdapter()->quoteInto(' AND `key` = ? ', $var);
        }
        $sql = $this->_getReadAdapter()->select()->from($table)->where($where);
        $rows = $this->_getReadAdapter()->fetchAll($sql);
        $return = array();
        foreach ($rows as $row) {
            $return[$row['key']] = $row['value'];
        }
        return $return;
    }

    /**
     * 
     * @param type $var
     * @param type $val
     * @return type
     */
    public function getByOrderMeta($var = '', $val = '') {
        $table = $this->getMainTable();
        $where = $this->_getReadAdapter()->quoteInto('`key` = ?', $var);
        if (!empty($val)) {
            $where .= $this->_getReadAdapter()->quoteInto(' AND `value` = ? ', $val);
        }
        $sql = $this->_getReadAdapter()->select()->from($table)->where($where);
        $sql = (string) $sql;
        
        $rows = $this->_getReadAdapter()->fetchAll((string)$sql);
        return $rows;
    }

    /**
     * 
     * @param type $order_id
     * @param type $var
     * @param type $val
     * @return type
     */
    public function getByOrderKeyValue($order_id, $var = '', $val = '') {
        $table = $this->getMainTable();
        $where = $this->_getReadAdapter()->quoteInto('order_id = ?', $order_id);
        if (!empty($var)) {
            $where .= $this->_getReadAdapter()->quoteInto(' AND `key` = ? ', $var);
        }
        if (!empty($val)) {
            $where .= $this->_getReadAdapter()->quoteInto(' AND `value` = ? ', $val);
        }
        $sql = $this->_getReadAdapter()->select()->from($table)->where($where);
        $rows = $this->_getReadAdapter()->fetchAll($sql);
        
        $return = array();
        foreach ($rows as $row) {
            $return[] = $row['order_id'];
        }
        return $return;
    }

}
