<?php

/*
 * Cron.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Contain all cron behavior
 */

class Cron extends StaticController {
    protected $class = 'Cron';
    protected $template = 'cron';
    private $classOptions = array('Home'=>array('display'));

    private $errors = array();
    private $reqData = array();

    function __construct($merge=false) {
        parent::__construct($merge);
        $this->reqData = parent::getRequest();
    }

    /**
     *
     * function: route
     * Routes a Cron call
     * @access public
     * @return [varied]
     */
    function route() {
        $controller = Request::getURI(1);
        $directive = Request::getURI(2);

        if (!empty($this->classOptions[$controller])) {
            $controller = new $controller();
            if (in_array($directive,$this->classOptions[$controller])) return $controller->$directive();
            else $this->errors[] = array(1,'Disallowed');
        } else $this->errors[] = array(1,'Disallowed');

        $this->tmp($this->template)->setVar('errors',$this->errors);
        return $this->tmp($this->template)->parse('error');
    }
}