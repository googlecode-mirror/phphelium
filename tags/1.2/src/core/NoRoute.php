<?php

/*
 * NoRoute.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all failed routing requests
 */

class NoRoute extends StaticController {
    protected $class = 'NoRoute';
    protected $template = 'noroute';
    public $cache = true;

    function __construct($merge=false) {
        parent::__construct($merge);
    }

    function action() {
        return $this->tmp($this->template)->render();
    }

    function display() {
        return $this->tmp($this->template)->render();
    }
}


