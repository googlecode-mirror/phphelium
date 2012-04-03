<?php

/*
 * Register.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Standard registration page (no styling)
 */

class Register extends StaticController {
    protected $class = 'Register';
    protected $template = 'register';
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
            $user = $user->register($this->reqData['username'],$this->reqData['password']);
            if (!empty($user)) {
                Session::setUser($user);
                return json_encode(array('result' => 'success', 'msg' => $this->tmp()->render('successRegister')));
            } else return json_encode(array('result' => 'error', 'msg' => $this->tmp()->render('failRegister')));
        } else return json_encode(array('result' => 'error', 'msg' => $this->tmp()->render('failRegister')));
    }

    function display() {
        return $this->tmp($this->template)->render();
    }

    public function logout() {
        Session::logout();
        header('Location: /');
    }
}

