<?php namespace Helium;

/*
 * Sitemap.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Standard Sitemap page
 */

class Sitemap extends StaticController {
    protected $class = 'Sitemap';
    protected $template = 'sitemap';
    public $cache = false;

    function __construct($merge=false) {
        parent::__construct($merge);
    }

    function action() {
        header('Content-type: text/xml;');
        return $this->output();
    }

    function display() {
        header('Content-type: text/xml;');
        $this->tmp($this->template)->setVar('sitemap','uri',DEFAULT_URI);
        return $this->output();
    }
}