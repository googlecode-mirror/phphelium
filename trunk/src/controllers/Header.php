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
        $language = new Language();
        $packs = $language->available();

        $languageData = array();
        if (!empty($packs)) {
            foreach($packs as $pack) {
                $pack = str_replace('.xml','',$pack);
                $languageData[] = (object)array('langId' => $pack,
                                                'selected' => (Session::getLanguage() == $pack ? ' selected' : ''));
            }
        }

        $this->tmp($this->template)->setVar('languages',$languageData);
        $this->tmp($this->template)->setVar('header','languages',$this->tmp($this->template)->render('languages',true));

        if (empty($this->user()->user_id)) $this->tmp($this->template)->setVar($this->template,'controls',$this->tmp('header')->render('outControls',true));
        else {
            $this->tmp($this->template)->setVar($this->template,'firstName',$this->user()->first_name);
            $this->tmp($this->template)->setVar($this->template,'controls',$this->tmp($this->template)->render('inControls',true));
        }

        return $this->tmp($this->template)->render();
    }
}