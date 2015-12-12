<?php

class Widgetized_Idpas400_Model_Resource_Order 
    extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('idpas400/order', 'id');
    }

    public function deleteByOrder($order_id, $var) {
        $table = $this->getMainTable();
        $where = $this->_getWriteAdapter()->quoteInto('order_id = ? AND ', $order_id)
                . $this->_getWriteAdapter()->quoteInto('`key` = ? 	', $var);
        $this->_getWriteAdapter()->delete($table, $where);
    }

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

}
