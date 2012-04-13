<?php

/*
 * APC.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle APC caching, including constants
 */

class APC {
    private $globalKey = 'core_constants';

    /**
     *
     * function: check
     * Checks to see if APC cache is loaded into memory
     * @access public
     * @param string $key
     * @return boolean
     */
    public function check($key) {
        if (apc_exists($key)) return true;
        else return false;
    }

    /**
     *
     * function: set
     * Sets an APC cache object
     * @access public
     * @param string $key
     * @return boolean
     */
    public function set($key,$value) {
        return apc_add($key,$value);
    }

    /**
     *
     * function: get
     * Gets an APC cache object
     * @access public
     * @param string $key
     * @return boolean
     */
    public function get($key) {
        return apc_fetch($key);
    }

    /**
     *
     * function: kill
     * Kills an APC cache object
     * @access public
     * @param string $key
     * @return boolean
     */
    public function kill($key) {
        return apc_delete($key);
    }

    /**
     *
     * function: checkConstants
     * Checks if global constants exist in APC
     * @access public
     * @return boolean
     */
    public function checkConstants() {
        if (apc_exists($this->globalKey)) return true;
        else return false;
    }

    /**
     *
     * function: setConstants
     * Loads global constants into APC
     * @access public
     * @param array $constants
     * @return boolean
     */
    public function setConstants($constants) {
        return apc_define_constants($this->globalKey,$constants);
    }

    /**
     *
     * function: loadConstants
     * Loads global constants into application memory
     * @access public
     * @return boolean
     */
    public function loadConstants() {
        return apc_load_constants($this->globalKey);
    }

    /**
     *
     * function: clearCache
     * Clear APC cache
     * @access public
     * @return boolean
     */
    public function clearCache() {
        return apc_clear_cache();
    }
}


