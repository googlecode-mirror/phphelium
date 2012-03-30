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
    private $errors;
    private $reqData;
    
    function __construct($merge=false) {
        $this->reqData = parent::getRequest($merge);
        
        if (!$this->user()->isAdmin()) {
            $this->tmp('error_html')->setVar('errors',array((object)array('id' => EID_BAD_LOGIN,
                                                                          'message' => 'This is not a valid API user')));
            
            exit($this->tmp('error_html')->render('error',true));
        }
    }
}


