<?php

/*
 * Request.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all request variables
 */

class Request {
    public $subdomain, $domain, $ext, $uri, $qstr, $params;

    function __construct() {
        $this->params = array();
        $this->populate();
    }

    /**
     *
     * function: populate
     * Populates the request containers
     * @access public
     * @return string
     */
    public function populate() {
        $host = explode('.',$_SERVER['HTTP_HOST']);
        if (count($host) == 3) {
            $this->subdomain = $host[0];
            $this->domain = $host[1];
            $this->ext = $host[2];
        } elseif (count($host) == 2) {
            $this->subdomain = 'www';
            $this->domain = $host[0];
            $this->ext = $host[1];
        }

        $this->uri = explode('/',$_SERVER['REQUEST_URI']);

        if (!empty($_SERVER['QUERY_STRING'])) {
            $qstring = explode('&',$_SERVER['QUERY_STRING']);
            $qstr = array();
            foreach($qstring as $q) {
                $q = explode('=',$q);
                $qstr[$q[0]] = $q[1];
            }

            $this->qstr = $qstr;
        } else $this->qstr = '';
    }

    /**
     *
     * function: getHost
     * Get global variable HTTP_HOST
     * @access public
     * @return string
     */
    public function getHost() {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     *
     * function: getSubDomain
     * Get subdomain information
     * @access public
     * @return string
     */
    public function getSubDomain() {
        $host = explode('.',$_SERVER['HTTP_HOST']);
        if (count($host) == 3) return $host[0];
        else return false;
    }

    /**
     *
     * function: getDomain
     * Get domain information
     * @access public
     * @return string
     */
    public function getDomain() {
        $host = explode('.',$_SERVER['HTTP_HOST']);
        if (count($host) == 3) return $host[1];
        else return $host[0];
    }

    /**
     *
     * function: getURIFrom
     * Gets URI chunk from a level
     * @access public
     * @param int $level (optional)
     * @return string
     */
    public function getURIFrom($level) {
        $uri = explode('/',$_SERVER['REQUEST_URI']);
        
        $cnt = 0;
        while($cnt < $level) { array_shift($uri); $cnt++; }
        return $uri;
    }

    /**
     *
     * function: getURI
     * Gets URI chunk
     * @access public
     * @param int $level (optional)
     * @return string
     */
    public function getURI($level=false) {
        $uri = explode('/',$_SERVER['REQUEST_URI']);
        return (!empty($level) ? (!empty($uri[$level]) && substr_count($uri[$level],'?') == 0 ? $uri[$level] : false) : $uri);
    }

    /**
     *
     * function: getLastURI
     * Gets last URI chunk
     * @access public
     * @return string
     */
    public function getLastURI() {
        $uri = explode('/',$_SERVER['REQUEST_URI']);
        return (!empty($uri[count($uri)-1]) ? $uri[count($uri)-1] : $uri[count($uri)-2]);
    }

    /**
     *
     * function: parse
     * Breaks up $_REQUEST variables into easily consumable variables
     * @access public
     * @param string $uri (optional)
     * @param array $funcs (optional)
     * @return string
     */
    public function parse($uri=false,$funcs=array('trim')) {
        if ($uri === false) $uri = $_SERVER['REQUEST_URI'];

        $arr = explode('/', preg_replace('/[?](.*)/', '', $uri));
        if (isset($arr)) array_shift($arr);
        else $arr = array();

        $allowedFuncs = array('trim', 'strip_tags', 'addslashes', 'stripslashes', 'urldecode');
        if (!is_array($funcs)) $funcs = array($funcs);

        foreach($_REQUEST as $key => $val) {
            foreach ($funcs as $func) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) if (in_array($func,$allowedFuncs) && is_callable($func)) $val[$k] = $func($v);
                } elseif (in_array($func,$allowedFuncs) && is_callable($func)) $val = $func($val);
            }

            $arr[$key] = $val;
        }

        $this->params = $arr;
    }

    /**
     *
     * function: getData
     * Get param data
     * @access public
     * @param into $numeric (optional)
     * @return string
     */
    public function getData($numeric=false) {
        $data = array();
        foreach ($this->params as $key => $val) {
            if ((!$numeric && !is_numeric($key)) || ($numeric && is_numeric($key)) && !empty($val)) $data[$key] = $val;
        }

        return $data;
    }
}



