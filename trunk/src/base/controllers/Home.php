<?php namespace Helium;

/*
 * Home.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Default page
 */

class Home extends StaticController {
    protected $class = 'Home';
    protected $template = 'home';
    public $cache = true;

    function __construct($merge=false) {
        parent::__construct($merge);
    }

    function action() {
        return $this->output();
    }

    function display() {
        $this->tmp($this->template)->setVar('home','documentation',$this->tmp('documentation')->render());
        return $this->output();
    }

    /**
     *
     * function: popupHelp
     * Controller for home-page popup
     * @access public
     * @return string
     */
    function popupHelp() {
        return $this->output('popupHelp');
    }
}

