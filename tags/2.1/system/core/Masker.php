<?php namespace Helium;

/*
 * Masker.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handles URI masking (for passing sensitive variables through the URI)
 */

class Masker {
    public static $ins = null;
    public static $key = null;

    public function __construct($key='aslkd7329rsklfr98tr') {
        self::$key = $key;
    }

    /**
     *
     * function: init
     * Loads the masker object
     * @access public
     * @return [Masker,boolean]
     */
    public function init($key=false) {
        if (self::$ins === null ) {
            self::$ins = new Masker($key);
        }

        return self::$ins;
    }

    /**
     *
     * function: mask
     * Masks a URI
     * @access public
     * @param string $id
     * @param string $key (optional)
     * @return string
     */
    public function mask($id,$key=false) {
        if (empty($key)) $key = self::$key;
        return self::encode($id,$key);
    }

    /**
     *
     * function: unmask
     * Unmasks a URI
     * @access public
     * @param string $str
     * @param string $key (optional)
     * @return string
     */
    public function unmask($str,$key=false) {
        if (empty($key)) $key = self::$key;
        return self::decode($str,$key);
    }

    /**
     *
     * function: encode
     * Encodes a string
     * @access public
     * @param string $string
     * @param string $key
     * @return string
     */
    private static function encode($string,$key) {
        $key = sha1($key);
        $strLen = 10;

        $keyLen = strlen($key);
        $hash ="";

        $j = 0;
        for ($i = 0; $i < $strLen; $i++) {
            $ordStr = ord(substr($string,$i,1));
            if ($j == $keyLen) $j = 0;
            $ordKey = ord(substr($key,$j,1));
            
            $j++;
            $hash .= strrev(base_convert(dechex($ordStr + $ordKey),16,36));
        }

        return $hash;
    }

    /**
     *
     * function: decode
     * Decodes a string
     * @access public
     * @param string $string
     * @param string $key
     * @return string
     */
    private static function decode($string,$key) {
        $key = sha1($key);
        $strLen = 10;

        $keyLen = strlen($key);
        $decoded ="";

        $j = 0;
        for ($i = 0; $i < $strLen; $i+=2) {
            $ordStr = hexdec(base_convert(strrev(substr($string,$i,2)),36,16));
            if ($j == $keyLen) $j = 0;
            $ordKey = ord(substr($key,$j,1));

            $j++;
            $decoded .= chr($ordStr - $ordKey);
        }

        return $decoded;
    }
}