<?php namespace Helium;

/*
 * Crypt.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all cryptographic functions (Required: mcrypt)
 */

class Crypt {
    private $td;
    private $iv;
    private $key;

    function __construct($key=false,$alg='rijndael-128',$mode='cbc') {
        if (empty($key)) $key = 'generic';
        if (!function_exists("mcrypt_module_open")) throw new Exception("Mcrypt module not installed.");
        if (!in_array($alg,mcrypt_list_algorithms())) $alg = 'rijndael-128';
        if (!in_array($mode,mcrypt_list_modes())) $mode = 'cbc';

        srand((double) microtime() * 1000000);

        $this->td = mcrypt_module_open($alg,'',$mode,'');
        $this->iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->td),MCRYPT_RAND);
        $this->key = substr($key,0,mcrypt_enc_get_key_size($this->td));
    }

    function __destruct() {
        mcrypt_module_close($this->td);
    }

    /**
     *
     * function: init
     * Prepare mcrypt to be used
     * @access public
     * @return boolean
     */
    private function init() {
        return (mcrypt_generic_init($this->td, $this->key, $this->iv) != -1);
    }

    /**
     *
     * function: encrypt
     * Encrypts a string based on a given algorithm
     * @access public
     * @param string $data
     * @param string $iv (optional)
     * @return string
     */
    public function encrypt($data,$iv=null) {
        $extra = 8 - (strlen($data) % 8);
        if ($extra > 0) {
            for ($i = 0; $i < $extra; $i++) $data .= "\0";
        }

        if ($iv !== null) $this->iv = $iv;
        if ($this->init()) {
            $encrypted = mcrypt_generic($this->td, $data);
            mcrypt_generic_deinit($this->td);

            return $this->iv.$encrypted;
        } else return false;
    }

    /**
     *
     * function: decrypt
     * Decrypts a string
     * @access public
     * @param string $data
     * @return string
     */
    public function decrypt($data) {
        $iv_size = mcrypt_enc_get_iv_size($this->td);
        $this->iv = substr($data, 0, $iv_size);
        $data = substr($data, $iv_size);

        if ($this->init()) {
            $decrypted = rtrim(mdecrypt_generic($this->td, $data));
            mcrypt_generic_deinit($this->td);
        } else return false;

        $last_char = substr($decrypted,-1);
        for ($i = 0; $i < $iv_size - 1; $i++) {
            if (chr($i) == $last_char) {
                $decrypted = substr($decrypted, 0, strlen($decrypted) - $i);
                break;
            }
        }

        return $decrypted;
    }

    /**
     *
     * function: urlEncode
     * Encode a URL-ready string
     * @access public
     * @param string $input
     * @return string
     */
    public function urlEncode($input) {
        return strtr(base64_encode($input), '+/=', '-_,');
    }

    /**
     *
     * function: urlDecode
     * Decode a URL-ready string
     * @access public
     * @param string $input
     * @return string
     */
    public function urlDecode($input) {
        return base64_decode(strtr($input, '-_,', '+/='));
    }
}

