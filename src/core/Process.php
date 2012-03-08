<?php

/*
 * Process.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all outgoing service requests
 */

class Process {
    function __construct() {}

    /**
     *
     * function: call
     * Make a cURL call
     * @access public
     * @param string $uri
     * @param mixed $data [optional]
     * @param tinyint $getData [optional]
     * @param string $username [optional]
     * @param string $password [optional]
     * @param string $methodOverride [optional]
     * @param boolean $mockBrowser [optional]
     * @param boolean $noSerialize [optional]
     * @return [mixed]
     */
    public function call($uri,$data=false,$getData=0,$username=false,$password=false,$methodOverride=false,$mockBrowser=false,$noSerialize=true) {
        if (is_array($data) && empty($noSerialize)) $data = array('data' => serialize($data));
        
        $cu = curl_init();

        curl_setopt($cu,CURLOPT_RETURNTRANSFER,1);

        if (!empty($mockBrowser)) {
            curl_setopt($cu,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
            curl_setopt($cu,CURLOPT_FOLLOWLOCATION,1);
            curl_setopt($cu,CURLOPT_MAXREDIRS,3);
        }

        if (empty($getData)) curl_setopt($cu,CURLOPT_TIMEOUT,1);
        
        if (!empty($username) && !empty($password)) curl_setopt($cu,CURLOPT_USERPWD,$username.':'.$password);
        if (empty($methodOverride) || strtolower($methodOverride) == 'post') {
            curl_setopt($cu,CURLOPT_URL,$uri);
            curl_setopt($cu,CURLOPT_POST,1);
            if (!empty($data)) curl_setopt($cu,CURLOPT_POSTFIELDS,$data);
        } elseif (strtolower($methodOverride) == 'get') {
            curl_setopt($cu,CURLOPT_HTTPGET,1);
            if (!empty($data)) curl_setopt($cu,CURLOPT_URL,$uri.'?'.$data);
            else curl_setopt($cu,CURLOPT_URL,$uri);
        }
        
	$data = curl_exec($cu);
        $info = curl_getinfo($cu);
        
        if (!empty($data)) return $data;
        return false;
    }

    /**
     *
     * function: outbound
     * Make an non-authenticated RESTful asynchronous service call
     * @access public
     * @param string $uri
     * @param mixed $data [optional]
     * @param string $methodOverride [optional]
     * @param boolean $mockBrowser [optional]
     * @return [mixed]
     */
    public function outbound($uri,$data=false,$methodOverride=false,$mockBrowser=false) {
        return $this->call($uri,$data,0,false,false,$methodOverride,$mockBrowser);
    }

    /**
     *
     * function: service
     * Make an non-authenticated RESTful service call
     * @access public
     * @param string $uri
     * @param mixed $data [optional]
     * @param string $methodOverride [optional]
     * @param boolean $mockBrowser [optional]
     * @return [mixed]
     */
    public function service($uri,$data=false,$methodOverride=false,$mockBrowser=false) {
        return $this->call($uri,$data,1,false,false,$methodOverride,$mockBrowser);
    }

    /**
     *
     * function: authOutbound
     * Make an authenticated RESTful asynchronous service call
     * @access public
     * @param string $uri
     * @param mixed $data [optional]
     * @param string $user
     * @param string $pass
     * @param string $methodOverride [optional]
     * @param boolean $mockBrowser [optional]
     * @return [mixed]
     */
    public function authOutbound($uri,$data=false,$user,$pass,$methodOverride=false,$mockBrowser=false) {
        return $this->call($uri,$data,0,$user,$pass,$methodOverride,$mockBrowser);
    }

    /**
     *
     * function: authService
     * Make an authenticated RESTful service call
     * @access public
     * @param string $uri
     * @param mixed $data [optional]
     * @param string $user
     * @param string $pass
     * @param string $methodOverride [optional]
     * @param boolean $mockBrowser [optional]
     * @return [mixed]
     */
    public function authService($uri,$data=false,$user,$pass,$methodOverride=false,$mockBrowser=false) {
        return $this->call($uri,$data,1,$user,$pass,$methodOverride,$mockBrowser);
    }

    /**
     *
     * function: authServiceRPC
     * Make an XML-RPC service call
     * @access public
     * @param string $uri
     * @param mixed $data [optional]
     * @param string $username [optional]
     * @param string $password [optional]
     * @return [mixed]
     */
    public function authServiceRPC($uri,$data=false,$username=false,$password=false) {
        $cu = curl_init();
        curl_setopt($cu,CURLOPT_URL,$uri);
        curl_setopt($cu,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($cu,CURLOPT_USERAGENT,'PHP XML-RPC Client');
        curl_setopt($cu,CURLOPT_POST,1);
        if (!empty($data)) curl_setopt($cu,CURLOPT_POSTFIELDS,$data);
        if (!empty($username) && !empty($password)) curl_setopt($cu,CURLOPT_USERPWD,$username.':'.$password);
        
        $data = curl_exec($cu);
        
        if (!empty($data)) return $data;
        return false;
    }
}

?>
