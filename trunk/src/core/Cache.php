<?php

/*
 * Cache.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle memcache functionality
 */

class Cache {
    private $instance;
    private $log = array();
    private $connected = false;

    function __construct($serverAppend=array()) {
        if (!is_object($this->instance)) $this->instance = new Memcache();
        $this->openServers($serverAppend);
    }

    /**
     *
     * function: openServers
     * Connects all memcached servers
     * @access public
     * @param array $serverAppend (optional)
     * @return [User,boolean]
     */
    public function openServers($serverAppend=array()) {
        if (defined('MEMCACHE_SERVERS')) $serverList = explode(';',MEMCACHE_SERVERS);
        if (!empty($serverAppend)) $serverList = array_merge($serverList,$serverAppend);

        $this->connected = false;
        if (!empty($serverList)) {
            foreach($serverList as $server) {
                $server = explode('@',$server);

                $this->instance->addServer($server[0],$server[1]);
                if ($this->instance->getServerStatus($server[0],$server[1])) {
                    $this->instance->pconnect($server[0],$server[1]) or false;
                    $this->connected = true;
                }
            }
        }
    }

    /**
     *
     * function: get
     * Gets a cache object
     * @access public
     * @param string $key
     * @return string
     */
    public function get($key) {
        if (!empty($this->connected) && systemCaching() == true) return $this->instance->get($key);
        else return false;
    }

    /**
     *
     * function: set
     * Sets a cache object
     * @access public
     * @param string $key
     * @param string $var
     * @param int $flag (optional)
     * @param int $timeout (optional)
     * @return string
     */
    public function set($key,$var,$flag=0,$expire=false) {
        if (empty($expire) && defined('MEMCACHE_DEFAULT_EXPIRY')) $expire = MEMCACHE_DEFAULT_EXPIRY;
        if (!empty($this->connected)) return $this->instance->set($key,$var,$flag,$expire);
        else return false;
    }

    /**
     *
     * function: add
     * Adds a cache object
     * @access public
     * @param string $key
     * @param string $var
     * @param int $flag (optional)
     * @param int $timeout (optional)
     * @return string
     */
    public function add($key,$var,$flag=0,$expire=false) {
        if (empty($expire) && defined('MEMCACHE_DEFAULT_EXPIRY')) $expire = MEMCACHE_DEFAULT_EXPIRY;
        if (!empty($this->connected)) return $this->instance->add($key,$var,$flag,$expire);
        else return false;
    }

    /**
     *
     * function: delete
     * Deletes a cache object
     * @access public
     * @param string $key
     * @param int $timeout (optional)
     * @return string
     */
    public function delete($key,$timeout = 0) {
        if (!empty($this->connected)) return $this->instance->delete($key,$timeout);
        else return false;
    }
}

?>