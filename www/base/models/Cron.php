<?php namespace Helium;

/*
 * Cron.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Contain all cron behavior
 */

class Cron {
    private $tmp;
    private $classOptions = array('Cron'=>array('info'));

    function __construct() {
        $this->tmp = Templater::init();
    }

    /**
     *
     * function: route
     * Routes a Cron call
     * @access public
     * @return [varied]
     */
    function route() {
        $var = Request::getURIFrom(2);
        $controller = (empty($var[1]) ? 'API' : $var[0]);
        $directive = (empty($var[1]) ? $var[0] : $var[1]);

        if (!empty($this->classOptions[$controller])) {
            $controller = new $controller();
            if (in_array($directive,$this->classOptions[$controller->class])) return $controller->$directive();
            else $this->err[] = array('id' => 1,'message' => 'Disallowed method');
        } else $this->err[] = array('id' => 2,'message' => 'Disallowed interface');

        header("Content-type: text/xml");
        $this->tmp('error_xml')->setVar('errors',$this->err);
        exit($this->tmp('error_xml')->render('error'));
    }

    /**
     *
     * function: info
     * API system-info call
     * @access public
     * @return [varied]
     */
    private function info() {
        exit('Cron is available and working');
    }
}