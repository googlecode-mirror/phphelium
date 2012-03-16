<?php

/*
 * Login.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Standard login page (no styling)
 */

class Login extends StaticController {
    protected $class = 'Login';
    protected $template = 'login';
    public $cache = false;

    private $errors = array();
    public $reqData = array();
	
    function __construct($merge=false) {
        parent::__construct();
        $this->reqData = $this->getRequest($merge);
    }

    function action() {
        $this->tmp()->setTemplate($this->template);
        if (!empty($this->reqData['username'])) {
            $user = new Users();
            $user = $user->login($this->reqData['username'],$this->reqData['password']);
            if (!empty($user)) return $this->tmp()->render('successLogin');
            else return $this->tmp()->render('failLogin');
        } else return $this->tmp()->render('failLogin');
    }

    function display() {
        return $this->tmp($this->template)->render();
    }

    public function logout() {
        Session::logout();
        header('Location: /');
    }
}

?>