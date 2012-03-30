<?php

/*
 * Model.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all data requests from application
 */

abstract class Model extends DB {
    protected $class = 'Model';
    protected $table;
    protected $primary;
    protected $validate;
    protected $schema;
    protected $cacheKeyId;

    protected $dataStore = array();
    protected $isUpdate;
    
    const expires = 2592000;

    function __construct($create=false) {
        if (!empty($create)) $this->buildSchema();
    }

    /**
     *
     * function: buildStatement
     * Builds an SQL statement from set variables
     * @access public
     * @param string $column
     * @param array $details
     * @return string
     */
    private function buildStatement($column,$details) {
        $sql = '"'.$column.'" '.$details['type'];
        if (!empty($details['isNull'])) $sql .= ' DEFAULT NULL';
        if (!empty($details['defaultValue'])) $sql .= ' DEFAULT "'.$details['defaultValue'].'"';

        return $sql;
    }

    /**
     *
     * function: buildSchema
     * Builds an SQL table creation statement from a model schema
     * @access public
     * @return boolean
     */
    public function buildSchema() {
        if (!empty($this->schema)) {
            $sql = 'CREATE TABLE IF NOT EXISTS '.$this->table.' (';

            $rows = array();
            foreach($this->schema as $column => $details) {
                $rows[] = $this->buildStatement($column,$details);
            }

            $sql .= implode(',',$rows);
            $sql .= ') ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;';
            
            return $this->create($sql);
        } else return false;
    }

    /**
     *
     * function: cacheKey
     * Gets the model cache key
     * @access public
     * @param [varied] $id
     * @return boolean
     */
    public function cacheKey($id) {
        return $this->class.'['.$id.']';
    }

    /**
     *
     * function: load
     * Loads the model data into memory for a primary key
     * @access public
     * @param int $id
     * @return Object
     */
    public function load($id) {
        $this->cacheKeyId = $id;
        
        $cache = new Cache();
        $cache_key = $this->cacheKey($id);
        $obj = $cache->get($cache_key);
        
        if (empty($obj)) {
            $sql = 'SELECT * FROM '.$this->table.' WHERE '.$this->primary.' = ?;';
            $result = $this->getAll($sql,array($id));
            if ($result) {
                $obj = array();
                foreach($result[0] as $id => $val) {
                    $obj[$id] = $val;
                }

                $cache->add($cache_key,serialize($obj));
            }
        } else $obj = unserialize($obj);
        
        if (!empty($obj)) {
            foreach($obj as $el => $d) $this->$el = $d;
        }
    }

    /**
     *
     * function: loadFromHash
     * Loads the model data into memory for a primary key by it's MD5 hash
     * @access public
     * @param string $hash
     * @return Object
     */
    public function loadFromHash($hash) {
        $sql = 'SELECT '.$this->primary.' FROM '.$this->table.' WHERE MD5('.$this->primary.') = ?;';
        $result = $this->getAll($sql,array($hash));
        if (!empty($result)) {
            $mid = $result[0]->{$this->primary};
            if (is_numeric($mid)) $this->load($mid);
        }
    }

    /**
     *
     * function: setData
     * Sets data into the store to be saved
     * @access public
     * @param string $data
     * @param string $value (optional)
     * @return boolean
     */
    public function setData($data,$value=false) {
        if (empty($value) && is_array($data)) {
            foreach($data as $did => $dval) {
                $this->dataStore[$did] = $dval;
            }
            
            return true;
        } elseif (!empty($value) && is_string($data)) {
            $this->dataStore[$data] = $value;
            return true;
        } else return false;
    }

    /**
     *
     * function: clear
     * Clears the cache
     * @access public
     * @int $id
     * @return null
     */
    public function clear($id) {
        $cache = new Cache();
        $cache_key = $this->cacheKey($id);
        $cache->delete($cache_key);
    }

    /**
     *
     * function: clearStore
     * Clears the data store
     * @access public
     * @return null
     */
    public function clearStore() {
        $this->dataStore = array();
        $this->isUpdate = null;
    }

    /**
     *
     * function: open
     * Opens a fresh data store
     * @access public
     * @param int $id
     * @return boolean
     */
    public function open($id=false) {
        $this->clearStore();
        if (empty($id)) $this->isUpdate = $id;
        else $this->isUpdate = false;

        return true;
    }

    /**
     *
     * function: validate
     * Checks rules for model against data set
     * @access public
     * @param array $data
     * @return boolean
     */
    public function validate($data=false) {
        if (empty($data) && !empty($this->dataStore)) $data = $this->dataStore;
        return Validate::checkAll($data,$this->validate);
    }

    /**
     *
     * function: save
     * Saves a data store (and maps data to store if provided)
     * @access public
     * @param array $data (optional)
     * @return boolean
     */
    public function save($data=false) {
        if (empty($data) && is_array($data)) $this->set($data);

        foreach($this->schema as $column => $details) {
            if (empty($this->dataStore[$column]) &&
                empty($details['isNull']) &&
                $column <> $this->primary) {
                    return false;
            }
        }
        
        foreach($this->dataStore as $column => $details) {
            if (empty($this->schema[$column])) unset($this->dataStore[$column]);
        }
        
        if (empty($this->isUpdate)) {
            $xml = $this->buildSQL($this->dataStore,false);
            if ($results = $this->insert($xml['sql'],$xml['inputs'])) {
                $this->clearStore();
                return $results;
            } else return false;
        } else {
            $xml = $this->buildSQL($this->dataStore,$this->isUpdate);
            if ($results = $this->update($xml['sql'],$xml['inputs'])) {
                $this->clearStore();
                return $results;
            } else return false;
        }
    }

    /**
     *
     * function: buildSQL
     * Builds an SQL statement for a data save
     * @access public
     * @param array $data
     * @param int $pid (optional)
     * @return boolean
     */
    public function buildSQL($data,$pid=false) {
        if (empty($pid)) {
            $els = array();
            $ins = array();

            foreach($this->schema as $did => $opts) {
                if ($did <> $this->primary) {
                    $els[] = $did;
                    $ins[] = (!empty($data[$did]) ? $data[$did] : false);
                }
            }

            $sql = 'INSERT INTO '.$this->table.' ('.implode(',',$els).') VALUES (?';
            for($subs=2;$subs<=count($this->schema)-1;$subs++) $sql .= ',?';
            $sql .= ');';

            return array('sql' => $sql,'inputs' => $ins);
        } else {
            $els = array();
            $ins = array();

            foreach($this->schema as $did => $opts) {
                $els[] = $did;
                $ins[] = (!empty($data[$did]) ? $data[$did] : false);
            }
            
            $sql = 'UPDATE '.$this->table.' SET ';

            foreach($data as $did => $dval) {
                $statements[] = $did.' = ?';
            }

            $sql .= implode(', '.$statements).' WHERE '.$this->primary.' = ?;';
            $ins[] = $pid;
            
            return array('sql' => $sql,'inputs' => $ins);
        }
    }

    /**
     *
     * function: getOne
     * Grab the top entry for an SQL statement
     * @access public
     * @param string $query
     * @param array $values (optional)
     * @return array
     */
    public function getOne($query=false,$values=array()) {
        if (empty($query)) $query = 'SELECT * FROM '.$this->table.' LIMIT 0,1;';
        return parent::getOne($query,$values);
    }

    /**
     *
     * function: getAll
     * Grab the entries for an SQL statement
     * @access public
     * @param string $query
     * @param array $values (optional)
     * @param array $noObjects (optional)
     * @return array
     */
    public function getAll($query=false,$values=array(),$noObjects=false) {
        if (empty($query)) $query = 'SELECT * FROM '.$this->table.';';
        return parent::getAll($query,$values,$noObjects);
    }

    /**
     *
     * function: loadAll
     * Load an array of objects by an array of primary keys
     * @access public
     * @param array $ids
     * @param string $sel (optional)
     * @param string $sdir (optional)
     * @return array
     */
    public function loadAll($ids,$sel=false,$sdir=false) {
        $sql = 'SELECT '.$this->primary.' FROM '.$this->table.' WHERE '.$this->primary.' IN (?)';
        if ($sel && $sdir) $sql .= ' ORDER BY '.$sel.' '.$sdir.';';

        $result = $this->getAll($sql,array($ids),true);
        if ($result) {
            $return = array();
            foreach($result as $row) {
                $rec = new $this->class;
                $rec->load($row);

                $return[] = $rec;
            }

            return $return;
        } else return false;
    }

    /**
     *
     * function: cache
     * Cache a model object
     * @access public
     * @param string $key
     * @param string $val
     * @param int $expire (optional)
     * @return boolean
     */
    public function cache($key,$val,$expire=0) {
        $cache = new Cache();
        $data = $cache->get($key);

        if ($data) $cache->delete($key);
        return $cache->add($key,$val,$expire);
    }

    /**
     *
     * function: purge
     * Purge a model cache
     * @access public
     * @param string $key
     * @return boolean
     */
    public function purge($key) {
        $cache = new Cache();
        return $cache->delete($key);
    }

    /**
     *
     * function: uncache
     * Purge the active model cache
     * @access public
     * @return boolean
     */
    public function uncache() {
        $cache = new Cache();
        $cache_key = $this->cacheKey($id);

	return $cache->delete($cache_key);
    }

    /**
     *
     * function: set
     * Set a cache object
     * @access public
     * @param string $key
     * @param string $val
     * @param int $expire (optional)
     * @return boolean
     */
    public function set($key,$val,$expire=0) {
        $cache = new Cache();
        $cache_key = $this->cacheKey($this->cacheKeyId);

        $key = $cache_key.'|'.$key;

        if ($this->cache($key,$val,$expire)) return true;
        else return false;
    }

    /**
     *
     * function: get
     * Get a cache object
     * @access public
     * @param string $key
     * @return string
     */
    public function get($key) {
        $cache = new Cache();
        $cache_key = $this->cacheKey($this->cacheKeyId);

        $key = $cache_key.'|'.$key;

        if ($obj = $cache->get($key)) return $obj;
        else return false;
    }
}


