<?php
namespace Helium;

/*
 * Page.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Render a standard page
 */

class Page extends Pager {
    function __construct() {
        if (!empty($_SERVER['HTTPS'])) {
            header('Location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
        }
    }

    /**
     *
     * function: build
     * Build a proper page
     * @access public
     * @param array $data
     * @return null
     */
    public function build($data = array('class'=>false,'title'=>DEFAULT_TITLE,'params'=>array(),'bypass'=>false,'cache'=>false)) {
        return parent::build($data);
    }
}