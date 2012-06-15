<?php

/*
 * Bitly.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Shorten links using the Bitly service
 */

class Bitly {
    private $username;
    private $apiKey;

    function __construct() {
        if (defined('BITLY_USERNAME')) $this->username = BITLY_USERNAME;
        if (defined('BITLY_API_KEY')) $this->apiKey = BITLY_API_KEY;
    }

    public function shorten($long,$username=false,$apiKey=false){
        if (empty($username)) $username = $this->username;
        if (empty($apiKey)) $apiKey = $this->apiKey;

        if (!empty($username) && !empty($apiKey)) {
            $url = 'http://api.bit.ly/shorten?version=2.0.1';
            $url .= '&longUrl='.$long;
            $url .= '&login='.$username;
            $url .= '&apiKey='.$apiKey;
            $url .= '&format=json&history=1';

            $cu = curl_init();
            curl_setopt($cu,CURLOPT_URL,$url);
            curl_setopt($cu,CURLOPT_HEADER,false);
            curl_setopt($cu,CURLOPT_RETURNTRANSFER,1);
            $result = curl_exec($cu);
            curl_close($cu);

            $obj = json_decode($result, true);
            return $obj['results'][$long]['shortUrl'];
        } else return false;
    }
}