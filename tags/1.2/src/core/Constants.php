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
     * function: clearConstants
     * Clear constants (if option available)
     * @access public
     * @return boolean
     */
    public function clearConstants() {
        if ($this->type == 'apc') {
            return $this->apc->clearCache();
        } else return false;
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
        // ----------------------------------------
        // database-specific constants..
        // ----------------------------------------
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

        // ----------------------------------------
        // system-specific constants..
        // ----------------------------------------
        $constants['ROOT'] = ((!empty($ini['system']['root']) && $ini['system']['root'] <> '@detect') ? $ini['system']['root'] : str_replace('src/core','',realpath(dirname(__FILE__))));
        $constants['SRC'] = ((!empty($ini['system']['src']) && $ini['system']['src'] <> '@detect') ? $ini['system']['src'] : $constants['ROOT'].'src/');
        
        $constants['LOG_LOCATION'] = (!empty($ini['system']['logs']) ? $ini['system']['logs'] : '/var/log/');
        $constants['DEFAULT_TEMPLATE_ROOT'] = $constants['ROOT'].'templates/';

        $constants['BASE_LOC'] = $constants['ROOT'];
        $constants['LOCAL_FILE_LOC'] = $constants['ROOT'].'files/';

        $constants['DISPLAY_ERRORS'] = (isset($ini['system']['displayErrors']) ? $ini['system']['displayErrors'] : 1);
        $constants['DEBUG_MODE'] = (isset($ini['system']['debugMode']) ? $ini['system']['debugMode'] : 1);
        
        $constants['CACHE_ALLOW'] = (isset($ini['system']['cacheAllow']) ? $ini['system']['cacheAllow'] : 1);
        $constants['SSL_AVAILABLE'] = (isset($ini['system']['sslAvailable']) ? $ini['system']['sslAvailable'] : 0);

        // ----------------------------------------
        // server-specific constants..
        // ----------------------------------------
        $constants['TIMEZONE'] = (!empty($ini['server']['timezone']) ? $ini['server']['timezone'] : 'UTC');
        if (!empty($ini['server']['subdomain'])) $constants['DEFAULT_SUBDOMAIN'] = $ini['server']['subdomain'];
        $constants['DEFAULT_URI'] = ((!empty($ini['server']['uri']) && $ini['server']['uri'] <> '@detect') ? $ini['server']['uri'] : $this->detectHost());
        $constants['VAR_PREPEND'] = (!empty($ini['server']['varPrepend']) ? $ini['server']['varPrepend'] : 'HEL_');

        // ----------------------------------------
        // mobile-specific constants..
        // ----------------------------------------
        if (!empty($ini['mobile'])) {
            $constants['AUTO_MOBILE'] = (isset($ini['mobile']['autoMobile']) ? $ini['mobile']['autoMobile'] : 0);
            $constants['MOBILE_SUBDOMAIN'] = (!empty($ini['mobile']['mobileSubdomain']) ? $ini['mobile']['mobileSubdomain'] : 'mobile');
        }

        // ----------------------------------------
        // api-specific constants..
        // ----------------------------------------
        if (!empty($ini['api'])) {
            $constants['SHARED_API_KEY'] = $ini['api']['sharedKey'];
        }

        // ----------------------------------------
        // memcache-specific constants..
        // ----------------------------------------
        if (!empty($ini['memcache'])) {
            $constants['MEMCACHE_SERVERS'] = $ini['memcache']['serverList'];
            $constants['MEMCACHE_DEFAULT_EXPIRY'] = (isset($ini['memcache']['defaultExpiry']) ? $ini['memcache']['defaultExpiry'] : 86400);
        }

        // ----------------------------------------
        // environment-specific constants..
        // ----------------------------------------
        if (empty($ini['environment']['title'])) $ini['environment']['title'] = "LANG[title]";
        if (empty($ini['environment']['keywords'])) $ini['environment']['keywords'] = "LANG[keywords]";
        if (empty($ini['environment']['description'])) $ini['environment']['description'] = "LANG[description]";
        if (empty($ini['environment']['summary'])) $ini['environment']['summary'] = "LANG[summary]";

        $constants['DEFAULT_TITLE'] = (preg_match('/LANG\[(.*?)\]/s',$ini['environment']['title']) ? Language::getSub(preg_replace(array('/LANG\[/','/\]/'),array('',''),$ini['environment']['title']),'GLOBAL') : $ini['environment']['title']);
        $constants['DEFAULT_KEYWORDS'] = (preg_match('/LANG\[(.*?)\]/s',$ini['environment']['keywords']) ? Language::getSub(preg_replace(array('/LANG\[/','/\]/'),array('',''),$ini['environment']['keywords']),'GLOBAL') : $ini['environment']['keywords']);
        $constants['DEFAULT_DESCRIPTION'] = (preg_match('/LANG\[(.*?)\]/s',$ini['environment']['description']) ? Language::getSub(preg_replace(array('/LANG\[/','/\]/'),array('',''),$ini['environment']['description']),'GLOBAL') : $ini['environment']['description']);
        $constants['DEFAULT_SUMMARY'] = (preg_match('/LANG\[(.*?)\]/s',$ini['environment']['summary']) ? Language::getSub(preg_replace(array('/LANG\[/','/\]/'),array('',''),$ini['environment']['summary']),'GLOBAL') : $ini['environment']['summary']);
        $constants['DEFAULT_LANGUAGE'] = (!empty($ini['environment']['language']) ? $ini['environment']['language'] : 'en');
        
        // ----------------------------------------
        // email-specific constants..
        // ----------------------------------------
        if (!empty($ini['email'])) {
            $constants['DEFAULT_EMAIL'] = (preg_match('/LANG\[(.*?)\]/s',$ini['email']['defaultFrom']) ? Language::getSub(preg_replace(array('/LANG\[/','/\]/'),array('',''),$ini['email']['defaultFrom']),'GLOBAL') : $ini['email']['defaultFrom']);
        }

        // ----------------------------------------
        // remaining constants..
        // ----------------------------------------
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

    /**
     *
     * function: detectHost
     * Determine proper host
     * @access public
     * @return string
     */
    private function detectHost() {
        $uri = parse_url($_SERVER['HTTP_HOST']);
        $host = explode('.', $uri['path']);
        $subdomains = array_slice($host,0,count($host)-2);
        
        return str_replace(implode('.',$subdomains).'.','',$_SERVER['HTTP_HOST']);
    }
}
