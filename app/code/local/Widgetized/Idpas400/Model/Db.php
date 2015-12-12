<?php

/**
 * 
 */
class Widgetized_Idpas400_Model_Db
{
    var $debug = true;
    
    /**
     *
     * @var type 
     */
    protected $_config = array(
        'host'     => 'IDPERP',
        'username' => 'B4Sprog',
        'password' => 'b4$pr09',
        // The database is not set here
//        'db_pro'   => 'ID2662AFB4', // product database
//        'db_tst'   => 'ID2662AFT4', // Test database
    );
    
    protected $_connection = null;
    
    /**
     * 
     */
    public function __construct()
    {
        $this->_connect();
    }
    
    /**
     * @return void
     */
    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }
        
        try {
            $this->_connection = odbc_connect(
                $this->_config['host'], 
                $this->_config['username'], 
                $this->_config['password']);
        } 
        catch(Exception $e) {
            if ($this->debug) Mage::log($e->getCode().' '.$e->getMessage(),null,'erp.log');
        }
    }
    
    /**
     * 
     * @param type $data
     * @param type $table
     * @param type $id
     */
    public function save( $data = array(), $table = false, $id = false ) {
        if (empty($data) || !$table) return false;
        
        $insert = (!$id || !isset($data[$id]) 
                || !$this->fetch_row("SELECT $id FROM $table WHERE $id='{$data[$id]}'"));
                
        return $insert
                ? $this->insert_row( $data, $table )
                : $this->update_row( $data, $table, $id );
    }
    
    /**
     * 
     * @param type $data
     * @param type $table
     * @param type $id
     */
    public function update_row( $data = array(), $table = false, $id = false ) {
        if (empty($data) || !$table || !$id) return false;
        
        $set = array();
        foreach((array)$data as $property => $value) {
            $value = addslashes(htmlspecialchars($value));
            $set[] = "$property='$value'";
        }
        $set = implode(',', $set);
        $sql = "UPDATE $table SET $set WHERE $id='{$data[$id]}'";
        Mage::log($sql,null,'insertRow.log');
        
        return $this->exec($sql);
    }
    
    /**
     * 
     * @param type $data
     * @param type $table
     */
    public function insert_row( $data = array(), $table = false, $debug = false ) {
        if (empty($data) || !$table) return false;
        
        $columns = implode(",", array_keys($data));
        
        $values = array_values($data);
        foreach ($values as $k => $v) {
            $values[$k] = addslashes(htmlspecialchars($v));
        }
        $values = "'".implode("','", $values)."'";
        $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        
        $result = $this->exec($sql, $debug);
        if ($debug) {
            Mage::helper('idpas400')->tr('Sql to insert', $sql, $debug);
            Mage::helper('idpas400')->tr('odbc response', $result?true:false, $debug);
//      }  else {
            Mage::log($result.' '.$sql,null,'insertRow.log');
        }
        
        if (!$result) {
            Mage::helper('idpas400')->tr('odbc exec error', odbc_errormsg(), $debug);
        }
        
        return $result;
    }
    
    /**
     * 
     * @param type $table
     * @param type $fields
     */
    public function fetch_array($sql) {
//        $resource = odbc_exec($this->_connection, $sql) or die("Error :" . odbc_errormsg());
        if (!$resource = $this->exec($sql)) return;
        
        $i = 0;
        $j = 0;
        $num_rows = 0;
        $thisData = array();

        try {
            // only populate select queries
            while (odbc_fetch_row($resource)) {
                $num_rows++;

                //Build tempory
                for ($j = 1; $j <= odbc_num_fields($resource); $j++) {
                    $field_name = odbc_field_name($resource, $j);
                    $ar[$field_name] = odbc_result($resource, $field_name) . "";
                }

                $thisData[$i] = $ar;
                $i++;
            }
        } 
        catch(Exception $e) {
            if ($this->debug) echo $e->getCode().' '.$e->getMessage();
        }

        return $thisData;
    }

    /**
     * 
     * @param type $table
     * @param type $fields
     */
    public function fetch_row( $sql )
    {
        $rows = $this->fetch_array($sql);
        return isset($rows[0]) ?$rows[0] :false;
    }
    
    /**
     * 
     */
    public function listTables() 
    {
        $result = odbc_tables($this->_connection);
//                or die("Error :".odbc_errormsg());
        
        $tables = array();
        try{
            while (odbc_fetch_row($result)) {
                if (odbc_result($result,"TABLE_TYPE")=="TABLE") {
                    $tables[] = odbc_result($result, "TABLE_NAME");
                }
            }
        } 
        catch(Exception $e) {
            if ($this->debug) echo $e->getCode().' '.$e->getMessage();
        }
        return $tables;
    }

    /**
     * Gets the last ID generated automatically by an IDENTITY/AUTOINCREMENT column.
     *
     * As a convention, on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2), this method forms the name of a sequence
     * from the arguments and returns the last id generated by that sequence.
     * On RDBMS brands that support IDENTITY/AUTOINCREMENT columns, this method
     * returns the last value generated for such a column, and the table name
     * argument is disregarded.
     *
     * Microsoft SQL Server does not support sequences, so the arguments to
     * this method are ignored.
     *
     * @param string $tableName   OPTIONAL Name of table.
     * @param string $primaryKey  OPTIONAL Name of primary key column.
     * @return string
     * @throws Zend_Db_Adapter_Exception
     */
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        $sql = 'SELECT SCOPE_IDENTITY()';
        return (int)$this->fetch_row($sql);
    }
    
    /**
     * 
     * @param type $sql
     */
    public function exec( $sql, $debug = false ) {
        Mage::helper('idpas400')->tr('odbc conenction', $this->_connection?true:false, $debug);
        if (!$this->_connection) {
//            Mage::log('No Connection was established',null,'erp.log');
            return;
        }
        try {
            $resource = odbc_exec($this->_connection, $sql);
        } 
        catch(Exception $e) {
            Mage::helper('idpas400')->tr('odbc exception error', $e->getCode().' '.$e->getMessage(), $debug);
           // echo $e->getCode().' '.$e->getMessage();
//            Mage::log('------------ Error table '.$table.' : '.$e->getCode().' '.$e->getMessage(),null,'erp.log');
        }
        return $resource;
    }
}
