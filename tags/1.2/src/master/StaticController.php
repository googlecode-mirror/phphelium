<?php

/*
 * StaticController.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Master controller for static page behavior
 */

abstract class StaticController extends Component {
    protected $class;
    protected $template;
    public $reqData, $err;
    
    function __construct($merge=false) {
        $this->reqData = parent::getRequest($merge);
        $this->err = false;
    }
}


