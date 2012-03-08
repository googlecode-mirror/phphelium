<?php

/*
 * API.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all RESTful API interactions
 */

class API extends APIController {
    protected $class = 'API';
    protected $template = 'api';
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
     * Routes an API call
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

        $this->tmp('error')->setVar('errors',$this->errors);
        return $this->tmp('error')->parse('xml_error');
    }
}

?>
