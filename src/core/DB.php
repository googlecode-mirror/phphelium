<?php

/*
 * DB.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all database transactions
 */

class DB {
    private $pointer = false;
    private $trans = false;
    private static $instance;

    function __construct($db=false,$pointer='slave') {
        if (empty($db) && defined('SLAVE_DB_STRING')) $db = SLAVE_DB_STRING;
        if (!empty($db)) $this->connect($db,$pointer);
        return true;
    }

    function __destruct() {
        $this->close();
    }

    public function __clone() {
        trigger_error('Cloning class DB is not allowed', E_USER_ERROR);
    }

    /**
     *
     * function: connect
     * Connect active DB to the right server
     * @access public
     * @param string $db
     * @param string $pointer
     * @return string
     */
    public function connect($db=false,$pointer='slave') {
        if (empty($db) && defined('SLAVE_DB_STRING')) $db = SLAVE_DB_STRING;
        if (!empty($db)) return $this->point($pointer,$db);
        else return false;
    }

    /**
     *
     * function: point
     * Point the active DB to the right server
     * @access public
     * @param string $pointer
     * @param string $db
     * @return Object
     */
    private function point($pointer,$db) {
        if ($this->pointer == $pointer && !empty($this->instance) && is_resource($this->instance)) return $this->instance;
        else {
            $db_parts = explode('@',$db);

            $db_connect = explode(';',$db_parts[0]);
            $db_user = explode(';',$db_parts[1]);

            $this->pointer = $pointer;
            $this->bridge($db_connect[0],$db_user[0],$db_user[1]);
            if ($this->instance) {
                $sdb = ($db_connect[1] ? $db_connect[1] : DEFAULT_DB);
                mysql_select_db($sdb,$this->instance);
                return $this->instance;
            } else return false;
        }
    }

    /**
     *
     * function: bridge
     * Start database connection
     * @access private
     * @param string $host
     * @param string $user
     * @param string $pass
     * @return null
     */
    private function bridge($host,$user,$pass,$attempts=0) {
        $this->instance = mysql_connect($host,$user,$pass) or false;
        if (empty($this->instance) && $attempts < 3) return $this->bridge($host,$user,$pass,$attempts++);
        else return false;
    }

    /**
     *
     * function: transaction
     * Start database transactions
     * @access public
     * @return null
     */
    public function transaction() {
        $this->pointMaster();
        $this->query('START TRANSACTION;');
        $this->trans = true;
    }

    /**
     *
     * function: commit
     * Commit database transactions
     * @access public
     * @return null
     */
    public function commit() {
        $this->pointMaster();
        $this->query('COMMIT;');
        $this->trans = false;
    }

    /**
     *
     * function: pointMaster
     * Point the database to the master servr
     * @access public
     * @return null
     */
    public function pointMaster() {
        if (defined('MASTER_DB_STRING')) return $this->point('master',MASTER_DB_STRING);
        else return false;
    }

    /**
     *
     * function: pointSlave
     * Point the database to the slave servr
     * @access public
     * @return null
     */
    public function pointSlave() {
        if (defined('SLAVE_DB_STRING')) return $this->point('slave',SLAVE_DB_STRING);
        else return false;
    }

    /**
     *
     * function: status
     * Check on the DB status
     * @access public
     * @return boolean
     */
    public function status() {
        if (!empty($this->instance)) return true;
        else return false;
    }

    /**
     *
     * function: query
     * Execute a query
     * @access public
     * @param string $query
     * @return boolean
     */
    private function query($query) {
        if (!$this->status()) $this->connect();
        if ($this->status()) {
            $result = mysql_query($query,$this->instance);

            if ($result) return $result;
            else return false;
        } else return false;
    }

    /**
     *
     * function: sanitize
     * Sanitize a variable for querying
     * @access public
     * @param string $value
     * @return string
     */
    public function sanitize($value) {
        if (is_array($value)) {
            foreach($value as $vid => $v) $value[$vid] = escape($v);
            return implode('\',\'',$value);
        } else return escape($value);
    }

    /**
     *
     * function: prepare
     * Prepare a query, sanitizing all variables
     * @access public
     * @param string $query
     * @param array $values
     * @return string
     */
    public function prepare($query,$values) {
        foreach($values as $value) {
            if (substr_count($query,'?')) {
                $value = $this->sanitize($value);
                $query = (empty($value) ? substr_replace($query,'null',strpos($query,"?"),1) : substr_replace($query,"'".$value."'",strpos($query,"?"),1));
            } else break;
        }

        return $query;
    }

    /**
     *
     * function: create
     * Run a 'create' statement
     * @access public
     * @param string $query
     * @param array $values (optional)
     * @return boolean
     */
    public function create($query,$values=array()) {
        $this->pointMaster();
        if (!empty($values)) $query = $this->prepare($query,$values);
        if ($result = $this->query($query)) return true;
        else return false;
    }

    /**
     *
     * function: insert
     * Run a 'insert' statement
     * @access public
     * @param string $query
     * @param array $values (optional)
     * @return int
     */
    public function insert($query,$values=array()) {
        $this->pointMaster();
        if (!empty($values)) $query = $this->prepare($query,$values);
        if ($result = $this->query($query)) return mysql_insert_id();
        else return false;
    }

    /**
     *
     * function: update
     * Run a 'update' statement
     * @access public
     * @param string $query
     * @param array $values (optional)
     * @return int
     */
    public function update($query,$values=array()) {
        $this->pointMaster();
        if (!empty($values)) $query = $this->prepare($query,$values);
        if ($result = $this->query($query)) return mysql_affected_rows();
        else return false;
    }

    /**
     *
     * function: delete
     * Run a 'delete' statement
     * @access public
     * @param string $query
     * @param array $values (optional)
     * @return boolean
     */
    public function delete($query,$values=array()) {
        $this->pointMaster();
        if (!empty($values)) $query = $this->prepare($query,$values);
        if ($result = $this->query($query)) return mysql_affected_rows();
        else return false;
    }

    /**
     *
     * function: getAll
     * Get all data from an executed query
     * @access public
     * @param string $query
     * @param array $values (optional)
     * @param array $noObjects (optional)
     * @param array $cache (optional)
     * @return array
     */
    public function getAll($query,$values=array(),$noObjects=false,$cache=false) {
        if (!empty($cache)) {
            if ($cache === true) $key = 'query['.md5($query).']';
            else $key = $cache;

            $c = new Cache();
            if ($arr = $c->get($key)) return unserialize($arr);
        }

        $this->pointSlave();
        if (!empty($values)) $query = $this->prepare($query,$values);
        $resource = $this->query($query);

        if ($resource) {
            $arr = array();
            while ($row = mysql_fetch_assoc($resource)) {
                if (!$noObjects) {
                    $obj = array();
                    foreach($row as $id => $el) $obj[$id] = $el;

                    $arr[] = (object)$obj;
                } else $arr[] = current($row);
            }

            mysql_free_result($resource);

            if (!empty($cache)) {
                if ($cache === true) $key = 'query['.md5($query).']';
                else $key = $cache;

                $c = new Cache();
                $c->set($key,serialize($arr));
            }

            return $arr;
        } else return false;
    }

    /**
     *
     * function: getOne
     * Get top data from an executed query
     * @access public
     * @param string $query
     * @param array $values (optional)
     * @param array $noObjects (optional)
     * @param array $cache (optional)
     * @return array
     */
    public function getOne($query,$values=array(),$noObjects=false,$cache=false) {
         if (!empty($cache)) {
            if ($cache === true) $key = 'query['.md5($query).']';
            else $key = $cache;

            $c = new Cache();
            if ($row = $c->get($key)) return unserialize($row);
        }

        $this->pointSlave();
        if (!empty($values)) $query = $this->prepare($query,$values);
        $resource = $this->query($query);

        if ($resource) {
            $arr = array();
            $row = mysql_fetch_row($resource);
            mysql_free_result($resource);

            if (!empty($cache)) {
                if ($cache === true) $key = 'query['.md5($query).']';
                else $key = $cache;

                $cache = new Cache();
                $cache->set($key,serialize($row));
            }

            return $row;
        } else return false;
    }

    /**
     *
     * function: close
     * Close the active connection
     * @access public
     * @return null
     */
    public function close() {
       if (!empty($this->instance) && is_resource($this->instance)) mysql_close($this->instance);
       unset($this->instance);
       $this->pointer = false;
    }
}

?>