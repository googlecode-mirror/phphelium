<?php
namespace Helium;

/*
 * StandardController.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Master controller for standard page behavior
 */

abstract class StandardController extends Component {
    protected $class;
    protected $template;
    public $reqData, $err;
    
    function __construct($merge=false) {
        $this->reqData = parent::getRequest($merge);
        $this->err = false;
        
        if (empty($this->user()->user_id)) header('Location: /login/');
    }
}


