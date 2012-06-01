<?php

/*
 * AdminController.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Master controller for Admin behavior
 */

abstract class AdminController extends Component {
    protected $class;
    protected $template;
    public $reqData, $err;
    
    function __construct($merge=false) {
        $this->reqData = parent::getRequest($merge);
        $this->err = false;

        if (empty($this->user()->user_id)) header('Location: /login/');
        elseif ($this->user()->isAdmin() == false) {
            $this->tmp('error_html')->setVar('errors',array((object)array('id' => (defined('EID_BAD_LOGIN') ? EID_BAD_LOGIN : 0),
                                                                          'message' => 'This is not a valid administrative user')));
            
            exit($this->tmp('error_html')->render('error',true));
        }
    }
}


