<?php

/*
 * Bitly.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Shorten links using the Bitly service
 */

class Bitly {
    function __construct() {
        // nothing to do here...
    }

    public function shorten($long,$username=BITLY_USERNAME,$apiKey=BITLY_API_KEY){
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
    }
}