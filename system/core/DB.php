<?php
namespace Helium;

/*
 * DB.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all database transactions
 */

class DB {
    private static $log = array();
    private $pointers = array('master' => false, 'slave' => false);
    private $pointer = false;
    private $trans = false;
    private static $instance;

    function __construct($master=false,$slave=false) {
        $this->buildPointers($master,$slave);
        if (!empty($this->pointers['master'])) $this->connect('master');
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
     * function: buildPointers
     * Setup pointers
     * @access public
     * @param string $master
     * @param string $slave
     * @return string
     */
    public function buildPointers($master=false,$slave=false) {
        if (!empty($master)) {
            $this->pointers['master'] = $master;

            if (empty($slave)) $this->pointers['slave'] = $master;
            else $this->pointers['slave'] = $slave;
        }

        if (defined('MASTER_DB_STRING') && empty($this->pointers['master'])) $this->pointers['master'] = MASTER_DB_STRING;
        if (defined('SLAVE_DB_STRING') && empty($this->pointers['slave'])) $this->pointers['slave'] = SLAVE_DB_STRING;
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
    public function connect($pointer='master',$db=false) {
        return $this->point($pointer,$db);
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
    private function point($pointer,$db=false) {
        if (empty($this->pointers[$pointer])) $this->buildPointers();
        if (!empty($db) && empty($this->pointers[$pointer])) $this->pointers[$pointer] = $db;

        if (!empty($this->pointers[$pointer])) {
            if ($this->pointer == $pointer && !empty($this->instance) && is_resource($this->instance)) return $this->instance;
            else {
                $db_parts = explode('@',$this->pointers[$pointer]);

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
        } else return false;
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
        return $this->point('master');
    }

    /**
     *
     * function: pointSlave
     * Point the database to the slave servr
     * @access public
     * @return null
     */
    public function pointSlave() {
        return $this->point('slave');
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
            if (!empty($_SESSION['system']['DEBUG'])) {
                self::$log[] = $query;
            }
            
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
                $query = (($value === '') ? substr_replace($query,'null',strpos($query,"?"),1) : substr_replace($query,"'".$value."'",strpos($query,"?"),1));
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

            $c = Cache::init();
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

                $c = Cache::init();
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

            $c = Cache::init();
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

                $cache = Cache::init();
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

    /**
     * Returns log.
     *
     * @access public
     * @return Array
     */
    public function getLog() {
        return self::$log;
    }
}

