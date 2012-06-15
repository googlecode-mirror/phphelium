<?php
namespace Helium;

/*
 * ErrorCodes.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Outputting non-fatal HTML errors
 */

class ErrorCodes extends StaticController {
    protected $class = 'ErrorCodes';
    protected $template = 'error_codes';
    public $cache = false;

    private $codeMap = array(401,403,404);

    function __construct($merge=false) {
        parent::__construct($merge);
    }

    function action() {
        return $this->output();
    }

    function display() {
        if (in_array($this->uri(2),$this->codeMap)) return $this->output($this->uri(2));
        else return $this->output('general');
    }
}


