<?php

/*
 * Login.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Standard login page (minimal styling)
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
            if (!empty($this->reqData['rememberMe'])) $sticky = true;
            else $sticky = false;

            $user = new Users();
            $user = $user->login($this->reqData['username'],$this->reqData['password'],$sticky);
            if (!empty($user)) return json_encode(array('result' => 'success', 'msg' => $this->tmp()->render('successLogin')));
            else return json_encode(array('result' => 'error', 'msg' => $this->tmp()->render('failLogin')));
        } else return json_encode(array('result' => 'error', 'msg' => $this->tmp()->render('failLogin')));
    }

    function display() {
        return $this->tmp($this->template)->render();
    }

    public function logout() {
        Session::logout();
        header('Location: /');
    }
}

