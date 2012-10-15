<?php namespace Helium;

/*
 * Footer.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handles footer-specific data
 */

class Footer extends Component {
    protected $class = 'Footer';
    protected $template = 'footer';
    public $reqData, $err;
    public $cache = false;

    function __construct($merge=false) {
        $this->reqData = parent::getRequest($merge);
        $this->err = false;
    }

    function action() {
        return $this->output();
    }

    function display() {
        return $this->output();
    }
}