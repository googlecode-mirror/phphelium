<?php

/*
 * Main.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Controller for all standard HTML requests (wrapping header and footer)
 */

class Main extends Component {
    protected $class = 'Main';
    protected $template = 'main';
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
        $this->tmp()->setTemplate($this->template);

        if (!empty($this->reqData['pageData']['title'])) $this->tmp()->setVar('main','pageTitle',$this->reqData['pageData']['title']);
        else $this->tmp()->setVar('main','pageTitle',DEFAULT_TITLE);
        
        if (!empty($this->reqData['pageData']['keywords'])) $this->tmp()->setVar('main','pageKeywords',$this->reqData['pageData']['keywords']);
        else $this->tmp()->setVar('main','pageKeywords',DEFAULT_KEYWORDS);

        if (!empty($this->reqData['pageData']['description'])) $this->tmp()->setVar('main','pageDescription',$this->reqData['pageData']['description']);
        else $this->tmp()->setVar('main','pageDescription',DEFAULT_DESCRIPTION);

        if (!empty($this->reqData['pageData']['summary'])) $this->tmp()->setVar('main','pageSummary',$this->reqData['pageData']['summary']);
        else $this->tmp()->setVar('main','pageSummary',DEFAULT_SUMMARY);

        $this->tmp()->setVar('main','pageUrl',(substr_count(DEFAULT_URI,'http') ? DEFAULT_URI : 'http://www.'.DEFAULT_URI).$_SERVER['REQUEST_URI']);
        $this->tmp()->setVar('main','pageImg',(substr_count(DEFAULT_URI,'http') ? DEFAULT_URI : 'http://www.'.DEFAULT_URI).'/images/logo.jpg');

        if (!empty($this->reqData['pageData']['cache'])) {
            $cached = Pager::getPageCache();
            if (!empty($cached)) $content = $cached;
        }

        if (empty($content)) {
            if (!empty($this->reqData['pageData']['content'])) {
                $class = new $this->reqData['pageData']['content']();
                $directive = (!empty($this->reqData['pageData']['directive']) ? $this->reqData['pageData']['directive'] : 'display');
                $content = $class->$directive();
            } else $content = Home::display();
        }
        
        if (!empty($this->reqData['pageData']['cache'])) Pager::setPageCache($content);
        
        $header = new Header();
        $this->tmp($this->template)->setVar('main','header',$header->display());

        $footer = new Footer();
        $this->tmp($this->template)->setVar('main','footer',$footer->display());

        $this->tmp($this->template)->setVar('main','content',$content);
        return $this->tmp($this->template)->render();
    }
}


