<?php

/*
 * Session.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all session behavior
 */

class Session {
    /**
     *
     * function: init
     * Initialize session information
     * @access public
     * @return string
     */
    public function init() {
        if ($uid = Session::getUserID()) return Session::setUser($uid);
        elseif ($uid = Cookie::getCookie('user')) return Session::setUser($uid);
    }

    /**
     *
     * function: getLanguage
     * Returns the system language
     * @access public
     * @return string
     */
    public function getLanguage() {
        if (!empty($_SESSION['language'])) return $_SESSION['language'];
        else {
            if ($user = Session::getUser()) {
                Session::setLanguage($user->language);
                return $user->language;
            } else {
                if (defined('DEFAULT_LANGUAGE')) $language = DEFAULT_LANGUAGE;
                else $language = 'en';
                
                Session::setLanguage($language);
                return $language;
            }
        }

        return false;
    }

    /**
     *
     * function: isMobile
     * Determine if the experience should be in mobile
     * @access public
     * @return boolean
     */
    public function isMobile() {
        if (isMobileBrowser() == true) {
            if (!empty($_SESSION['noMobile'])) return false;
        } else return false;

        return true;
    }

    /**
     *
     * function: noMobile
     * Set mobile preference
     * @access public
     * @return boolean
     */
    public function noMobile() {
        $_SESSION['noMobile'] = true;
        return true;
    }

    /**
     *
     * function: setLanguage
     * Sets the system language
     * @access public
     * @param string $lang
     * @return null
     */
    public function setLanguage($lang) {
        if (!empty($_SESSION)) {
            unset($_SESSION['language']);
            $_SESSION['language'] = $lang;
        }
    }

    /**
     *
     * function: setLocale
     * Sets the system location
     * @access public
     * @param int $location_id
     * @return null
     */
    public function setLocale($location_id) {
        $_SESSION['locations']['actual'] = $location_id;
    }

    /**
     *
     * function: setAppVar
     * Sets an application variable
     * @access public
     * @param string $el
     * @param string $var
     * @return null
     */
    public function setAppVar($el,$var) {
        $_SESSION['app'][$el] = $var;
        return $var;
    }

    /**
     *
     * function: getAppVar
     * Gets a system variable
     * @access public
     * @param string $el
     * @return [varies]
     */
    public function getAppVar($el) {
        return (!empty($_SESSION['app'][$el]) ? $_SESSION['app'][$el] : false);
    }

    /**
     *
     * function: isDebug
     * Determines if the system is in debug mode
     * @access public
     * @return boolean
     */
    public function isDebug() {
        if (empty($_SESSION['system']['debug'])) $_SESSION['system']['debug'] = DEBUG_MODE;
        if (!empty($_SESSION['system']['debug'])) return true;
        else return false;
    }

    /**
     *
     * function: forceDebug
     * Forces the system into debug mode
     * @access public
     * @return null
     */
    public function forceDebug() {
        ini_set('display_errors',1);
        $_SESSION['system']['debug'] = true;
    }

    /**
     *
     * function: endDebug
     * Forces the system out of debug mode
     * @access public
     * @return null
     */
    public function endDebug() {
        ini_set('display_errors',0);
        unset($_SESSION['system']['debug']);
    }

    /**
     *
     * function: getCustomCSS
     * Gets custom CSS files for URI divisions
     * @access public
     * @return [boolean,string]
     */
    public function getCustomCSS() {
        if ($_SESSION['config']['account']['css']) return $_SESSION['config']['account']['css'];
        else return false;
    }

    /**
     *
     * function: setCustomURI
     * Sets custom URI data
     * @access public
     * @return boolean
     */
    public function setCustomURI() {
        if ($_SERVER['HTTP_HOST'] <> DEFAULT_URI) {
            $_SESSION['config']['customURI'] = $_SERVER['HTTP_HOST'];
            return true;
        } else return false;
    }

    /**
     *
     * function: getUserID
     * Gets the active user's ID
     * @access public
     * @return [boolean,int]
     */
    public function getUserID() {
        if (!empty($_SESSION['user'])) return $_SESSION['user'];
        else return false;
    }

    /**
     *
     * function: getUser
     * Gets the active user's account
     * @access public
     * @return [boolean,User]
     */
    public function getUser() {
        if ($user_id = Session::getUserID()) {
            $user = new User();
            $user->load($user_id);

            return $user;
        } else return false;
    }

    /**
     *
     * function: setUser
     * Gets the active user's account
     * @access public
     * @param int $user_id
     * @return null
     */
    public function setUser($user_id) {
        $_SESSION['user'] = $user_id;
    }

    /**
     *
     * function: logout
     * Destroys active user session and login credentials
     * @access public
     * @return null
     */
    public function logout() {
        Cookie::destroyCookie('user');
        unset($_SESSION['user']);
        unset($_SESSION);
    }

    /**
     *
     * function: setSuccessMessage
     * Set session success messaging
     * @access public
     * @param string $msg
     * @return null
     */
    public function setSuccessMessage($msg) {
        $_SESSION['messages'] = array();
        $_SESSION['messages']['msg'] = $msg;
        $_SESSION['messages']['type'] = 'success';
    }

    /**
     *
     * function: setErrorMessage
     * Set session error messaging
     * @access public
     * @param string $msg
     * @return null
     */
    public function setErrorMessage($msg) {
        $_SESSION['messages'] = array();
        $_SESSION['messages']['msg'] = $msg;
        $_SESSION['messages']['type'] = 'error';
    }

    /**
     *
     * function: getMessaging
     * Gets session global messaging
     * @access public
     * @return null
     */
    public function getMessaging() {
        if (!empty($_SESSION['messages'])) return $_SESSION['messages'];
        else return false;
    }

    /**
     *
     * function: resetMessaging
     * Resets session global messaging
     * @access public
     * @return null
     */
    public function resetMessaging() {
        $_SESSION['messages'] = false;
        unset($_SESSION['messages']);
    }
}


