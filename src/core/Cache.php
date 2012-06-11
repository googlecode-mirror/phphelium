<?php

/*
 * Cache.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle memcache functionality
 */

class Cache extends Memcache {
    private static $instance;
    private $log = array();
    private $connected = false;

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

        self::$instance->connected = false;
        if (!empty($serverList)) {
            foreach($serverList as $server) {
                $server = explode('@',$server);

                self::$instance->addServer($server[0],$server[1]);
                if (self::$instance->getServerStatus($server[0],$server[1])) {
                    self::$instance->pconnect($server[0],$server[1]) or false;
                    self::$instance->connected = true;
                }
            }
        }
    }

    /**
     *
     * function: init
     * Loads the cache object
     * @access public
     * @return [Cache,boolean]
     */
    public static function init() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        self::openServers();
        return self::$instance;
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
        if (!empty($this->connected) && systemCaching() == true) return parent::get(VAR_PREPEND.$key);
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
        if (!empty($this->connected)) return parent::set(VAR_PREPEND.$key,$var,$flag,$expire);
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
        if (!empty($this->connected)) return parent::add(VAR_PREPEND.$key,$var,$flag,$expire);
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
        if (!empty($this->connected)) return parent::delete(VAR_PREPEND.$key,$timeout);
        else return false;
    }

    /**
     *
     * function: flush
     * Flush all memcache objects
     * @access public
     * @return string
     */
    public function flush() {
        if (!empty($this->connected)) return parent::flush();
        else return false;
    }
}
