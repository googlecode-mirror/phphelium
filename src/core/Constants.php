<?php

/*
 * Constants.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all constant behavior
 */

class Constants {
    private $type;
    private $apc;
    private $protectedIni = array('database','system','server','mobile','api','memcache','environment','email');

    function __construct($noAPC=false) {
        if (empty($noAPC) && function_exists('apc_define_constants')) {
            require_once(realpath(dirname(__FILE__)).'/APC.php');
            $this->apc = new APC();
            $this->type = 'apc';
        } else $this->type = 'standard';
    }

    /**
     *
     * function: checkConstants
     * Checks to see if constants are already loaded
     * @access public
     * @return boolean
     */
    public function checkConstants() {
        if ($this->type == 'apc') {
            if ($this->apc->checkConstants()) {
                $this->apc->loadConstants();
                return true;
            }
        }

        return false;
    }

    /**
     *
     * function: buildConstants
     * Builds constants from ini file and loads into memory
     * @access public
     * @param array $ini
     * @param array $constants (optional)
     * @return boolean
     */
    public function buildConstants($ini,$constants=array()) {
        if (!empty($ini['database'])) {
            $constants['MASTER_DB_STRING'] = $ini['database']['master'];
            unset($ini['database']['master']);

            $constants['SLAVE_DB_STRING'] = $ini['database']['slave'];
            unset($ini['database']['slave']);

            $constants['DEFAULT_DB'] = $ini['database']['default'];
            unset($ini['database']['default']);

            if (!empty($ini['database'])) {
                foreach($ini['database'] as $db => $conn) {
                    $constants[strtoupper($db).'_DB_STRING'] = $conn;
                }
            }
        }

        if (!empty($ini['system'])) {
            $constants['ROOT'] = $ini['system']['root'];
            $constants['SRC'] = $ini['system']['src'];
            $constants['LOG_LOCATION'] = $ini['system']['logs'];
            $constants['DEFAULT_TEMPLATE_ROOT'] = $ini['system']['root']."templates/";

            $constants['BASE_LOC'] = $ini['system']['root'];
            $constants['LOCAL_FILE_LOC'] = $ini['system']['root']."files/";

            $constants['DISPLAY_ERRORS'] = $ini['system']['displayErrors'];
            $constants['DEBUG_MODE'] = $ini['system']['debugMode'];

            $constants['CACHE_ALLOW'] = $ini['system']['cacheAllow'];
        }

        if (!empty($ini['server'])) {
            $constants['TIMEZONE'] = (!empty($ini['server']['timezone']) ? $ini['server']['timezone'] : 'UTC');
            if ($ini['server']['subdomain']) $constants['DEFAULT_SUBDOMAIN'] = $ini['server']['subdomain'];
            $constants['DEFAULT_URI'] = $ini['server']['uri'];
            $constants['VAR_PREPEND'] = $ini['server']['varPrepend'];
        }

        if (!empty($ini['mobile'])) {
            $constants['AUTO_MOBILE'] = $ini['mobile']['autoMobile'];
            $constants['MOBILE_SUBDOMAIN'] = $ini['mobile']['mobileSubdomain'];
        }

        if (!empty($ini['api'])) {
            $constants['SHARED_API_KEY'] = $ini['api']['sharedKey'];
        }

        if (!empty($ini['memcache'])) {
            $constants['MEMCACHE_SERVERS'] = $ini['memcache']['serverList'];
            $constants['MEMCACHE_DEFAULT_EXPIRY'] = $ini['memcache']['defaultExpiry'];
        }

        if (!empty($ini['environment'])) {
            $constants['DEFAULT_TITLE'] = (preg_match('/LANG\[(.*?)\]/s',$ini['environment']['title']) ? Language::getSub(preg_replace(array('/LANG\[/','/\]/'),array('',''),$ini['environment']['title']),'GLOBAL') : $ini['environment']['title']);
            $constants['DEFAULT_KEYWORDS'] = (preg_match('/LANG\[(.*?)\]/s',$ini['environment']['keywords']) ? Language::getSub(preg_replace(array('/LANG\[/','/\]/'),array('',''),$ini['environment']['keywords']),'GLOBAL') : $ini['environment']['keywords']);
            $constants['DEFAULT_DESCRIPTION'] = (preg_match('/LANG\[(.*?)\]/s',$ini['environment']['description']) ? Language::getSub(preg_replace(array('/LANG\[/','/\]/'),array('',''),$ini['environment']['description']),'GLOBAL') : $ini['environment']['description']);
            $constants['DEFAULT_SUMMARY'] = (preg_match('/LANG\[(.*?)\]/s',$ini['environment']['summary']) ? Language::getSub(preg_replace(array('/LANG\[/','/\]/'),array('',''),$ini['environment']['summary']),'GLOBAL') : $ini['environment']['summary']);
            $constants['DEFAULT_LANGUAGE'] = $ini['environment']['language'];
        }

        if (!empty($ini['email'])) {
            $constants['DEFAULT_EMAIL'] = (preg_match('/LANG\[(.*?)\]/s',$ini['email']['defaultFrom']) ? Language::getSub(preg_replace(array('/LANG\[/','/\]/'),array('',''),$ini['email']['defaultFrom']),'GLOBAL') : $ini['email']['defaultFrom']);
        }

        foreach($ini as $iid => $idata) {
            if (!in_array($iid,$this->protectedIni)) {
                foreach($idata as $constKey => $constValue) {
                    $constants[$constKey] = (preg_match('/LANG\[(.*?)\]/s',$constValue) ? Language::getSub(preg_replace(array('/LANG\[/','/\]/'),array('',''),$constValue),'GLOBAL') : $constValue);
                }
            }
        }

        return $constants;
    }

    /**
     *
     * function: setConstants
     * Sets constants using whatever approach desired
     * @access public
     * @param array $constants
     * @return boolean
     */
    public function setConstants($constants) {
        if ($this->type == 'standard') {
            foreach($constants as $cid => $c) define($cid,$c);
        } else return $this->apc->setConstants($constants);
    }
}

