<?php

/*
 * Header.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handles header-specific data
 */

class Header extends Component {
    protected $class = 'Header';
    protected $template = 'header';
    public $reqData, $err;
    public $cache = false;

    function __construct($merge=false) {
        $this->reqData = parent::getRequest($merge);
        $this->err = false;
    }

    function action() {
        return $this->tmp($this->template)->render();
    }

    function display() {
        if (empty($this->user()->user_id)) {
            $this->tmp('header')->setVar('header','controls',$this->tmp('header')->render('outControls',true));
        } else {
            $this->tmp('header')->setVar('header','controls',$this->tmp('header')->render('inControls',true));
        }

        return $this->tmp($this->template)->render();
    }
}