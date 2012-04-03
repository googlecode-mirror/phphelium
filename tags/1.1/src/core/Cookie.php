<?php

/*
 * Cookie.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Manage cookies
 */

class Cookie {
    public function __clone() {
        trigger_error('Cloning class Cookie is not allowed', E_USER_ERROR);
    }

    /**
     *
     * function: setCookie
     * Sets a system cookie
     * @access public
     * @param string $id
     * @param string $value
     * @return null
     */
    public function setCookie($id,$value) {
        if (empty($_COOKIE[VAR_PREPEND.$id])) {
            setcookie(VAR_PREPEND.$id,$value,strtotime('+14 days'),'/',Cookie::baseURI(),false,true);
        }
    }

    /**
     *
     * function: getCookie
     * Gets a system cookie
     * @access public
     * @param string $id
     * @return string
     */
    public function getCookie($id) {
        if (!empty($_COOKIE[VAR_PREPEND.$id])) return $_COOKIE[VAR_PREPEND.$id];
        else return false;
    }

    /**
     *
     * function: destroyCookie
     * Destroys a system cookie
     * @access public
     * @param string $id
     * @return null
     */
    public function destroyCookie($id) {
        setcookie(VAR_PREPEND.$id,'',strtotime('-1 hour'),'/',Cookie::baseURI(),false,true);
    }

    public function baseURI() {
        $base = '';
        if (defined('DEFAULT_URI')) $base = DEFAULT_URI;
        if (defined('DEFAULT_SUBDOMAIN')) $base = DEFAULT_SUBDOMAIN.$base;

        return $base;
    }
}

