<?php

/*
 * StandardController.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Master controller for standard page behavior
 */

abstract class StandardController extends Component {
    protected $class;
    protected $template;
    private $errors;
    private $reqData;
    
    function __construct($merge=false) {
        $this->reqData = parent::getRequest($merge);
        
        if (!$this->user()) {
            $this->tmp('error_html')->setVar('errors',array((object)array('id' => EID_ACCESS_DENIED,
                                                                          'message' => 'You must be logged in to view this page')));
            
            exit($this->tmp('error_html')->render('error',true));
        }
    }
}

?>
