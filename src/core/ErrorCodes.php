<?php

/*
 * ErrorCodes.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Outputting non-fatal HTML errors
 */

class ErrorCodes extends StaticController {
    protected $class = 'ErrorCodes';
    protected $template = 'error_codes';
    private $codeMap = array(401,403,404);
    
    private $errors = array();
    private $reqData = array();

    function __construct($merge=false) {
        parent::__construct($merge);
        $this->reqData = parent::getRequest();
    }

    function action() {
        $this->tmp()->setTemplate($this->template);

    }

    function display() {
        $this->tmp()->setTemplate($this->template);
        if (in_array($this->uri(2),$this->codeMap)) return $this->tmp()->render($this->uri(2),true);
        else return $this->tmp()->render('general',true);
    }
}

?>
