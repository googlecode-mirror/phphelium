<?php

/*
 * Component.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Wrapper for all subcontrollers
 */

abstract class Component extends DB {
    private $tmp;
    private $req = array();

    function __construct() {
        $this->tmp = Templater::init();
        $this->prepareReq();
    }

    /**
     *
     * function: clearAll
     * Clear page cache data
     * @access public
     * @return string
     */
    public function clearAll() {
        $cache = new Cache();
        $cache->delete(VAR_PREPEND.'store[environment[pageCache]['.md5($_SERVER['REQUEST_URI']).']');
    }

    /**
     *
     * function: uri
     * Get a URI chunk
     * @access public
     * @param int $level
     * @return null
     */
    public function uri($level,$from=false) {
        if (empty($from)) return Request::getURI($level);
        else return Request::getURIFrom($level);
    }

    /**
     *
     * function: prepareReq
     * Prepares all request data for each component to use
     * @access public
     * @return null
     */
    private function prepareReq() {
        $req = new Request();
        $req->parse();

        $this->req = $req->getData();
    }

    /**
     *
     * function: getRequest
     * Gets all request variables
     * @access public
     * @param array $merge (optional)
     * @return array
     */
    public function getRequest($merge=false) {
        if (empty($this->req)) $this->prepareReq();
        if (!empty($merge)) $this->req = array_merge($this->req,$merge);
        return $this->req;
    }

    /**
     *
     * function: session
     * Gets session object for manipulation by controller, or sets session application variable
     * @access public
     * @param string $el [optional]
     * @param string $var [optional]
     * @return mixed
     */
    public function session($el=false,$var=false) {
        $session = new Session();
        if (!empty($el)) {
            if (!empty($var)) return $session->setAppVar($el,$var);
            else return $session->getAppVar($el);
        } else return $session;
    }

    /**
     *
     * function: process
     * Gets process object
     * @access public
     * @param String $uri [optional]
     * @param boolean $getData [optional]
     * @param String $username [optional]
     * @param String $password [optional]
     * @return mixed
     */
    public function process($uri=false,$data=false,$getData=false,$username=false,$password=false) {
        $process = new Process();
        if (!empty($uri)) {
            if (empty($getData)) $func = 'outbound';
            else $func = 'service';

            if (!empty($username) && !empty($password)) {
                $func = 'auth'.ucwords($func);
                $results = $process->$func($uri,$data,$username,$password);
            } else $results = $process->$func($uri,$data);
        } else return $process;
    }

    /**
     *
     * function: cache
     * Gets cache object
     * @access public
     * @return Cache
     */
    public function cache($el=false,$val=false) {
        $cache = new Cache();
        if (!empty($el)) {
            if (!empty($var)) return $cache->set($el,$var);
            else return $cache->get($el);
        } else return $cache;
    }

    /**
     *
     * function: crypt
     * Gets crypt object
     * @access public
     * @param string $key
     * @param string $algorithm (optional)
     * @param string $mode (optional)
     * @return Crypt
     */
    public function crypt($key,$algorithm=false,$mode=false) {
        return new Crypt($key,$algorithm,$mode);
    }

    /**
     *
     * function: email
     * Gets email object, and can change email templates if desired
     * @access public
     * @param string $tmp (optional)
     * @return Email
     */
    public function email($tmp=false) {
        return new Email($tmp);
    }

    /**
     *
     * function: log
     * Gets log object
     * @access public
     * @param boolean $onlyInDebug (optional)
     * @param string $writeFile (optional)
     * @return Log
     */
    public function log($onlyInDebug=true,$writeFile='activity.log') {
        $settings = array('writeFile' => $writeFile);
        if ($onlyInDebug == true && Session::isDebug() == false) $settings['mockOnly'] = true;

        $log = new Log($settings);
        return $log;
    }

    /**
     *
     * function: tmp
     * Gets tmp object, and can change templates if desired
     * @access public
     * @param string $tmp (optional)
     * @return Templater
     */
    function tmp($tmp=null) {
        if (!is_object($this->tmp)) $this->tmp = Templater::init();
        if (!empty($tmp)) $this->tmp->setTemplate($tmp);
        return $this->tmp;
    }

    /**
     *
     * function: user
     * Gets user object, if exists
     * @access public
     * @return [User,boolean]
     */
    function user() {
        return Session::getUser();
    }

    /**
     *
     * function: apiUser
     * Gets user object from API call, if exists
     * @access public
     * @return [User,boolean]
     */
    function apiUser() {
        if (!empty($this->req['username']) && !empty($this->req['key'])) {
            return Users::isAPIRegistered($this->req['username'],$this->req['key']);
        } else return false;
    }

    function action() {}
    function display() {}
}

?>
