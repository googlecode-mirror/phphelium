<?php

/*
 * Home.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Default page
 */

class Home extends StaticController {
    protected $class = 'Home';
    protected $template = 'home';
    public $cache = true;
    
    private $errors = array();
    public $reqData = array();
	
    function __construct($merge=false) {
        parent::__construct();
        $this->reqData = $this->getRequest($merge);
    }

    function action() {
        return $this->tmp($this->template)->render($this->template,true);
    }

    function display() {
        $this->tmp($this->template)->setVar('home','documentation',$this->tmp('documentation')->render());
        return $this->tmp($this->template)->render();
    }
    
    function popupHelp() {
        return $this->tmp($this->template)->render('popupHelp',true);
    }
}

?>