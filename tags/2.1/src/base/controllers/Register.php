<?php namespace Helium;

/*
 * Register.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Standard registration page (minimal styling)
 */

class Register extends StaticController {
    protected $class = 'Register';
    protected $template = 'register';
    public $cache = false;

    function __construct($merge=false) {
        parent::__construct($merge);
    }

    function action() {
        if (!empty($this->reqData['username'])) {
            $info = array();
            if (!empty($this->reqData['first_name'])) $info['first_name'] = $this->reqData['first_name'];
            if (!empty($this->reqData['last_name'])) $info['last_name'] = $this->reqData['last_name'];

            $user = new Users();
            $user = $user->register($this->reqData['username'],$this->reqData['password'],$info);
            if (!empty($user)) {
                Session::setUser($user);
                return json_encode(array('result' => 'success',
                                         'msg' => $this->tmp($this->template)->render('success')));
            } else {
                return json_encode(array('result' => 'error',
                                         'msg' => $this->tmp($this->template)->render('fail')));
            }
        } else {
            return json_encode(array('result' => 'error',
                                     'msg' => $this->tmp($this->template)->render('fail')));
        }
    }

    function display() {
        return $this->output();
    }
}

