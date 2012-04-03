<?php

/*
 * Email.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all emailing behavior
 */

class Email {
    private $defaultFrom = false;
    private $tmp = false;
    private $tmpPrior = false;
    private $template = 'email';

    public function __construct($template=false) {
        $this->prepareTmp();

        if (defined('DEFAULT_EMAIL')) $this->defaultFrom = DEFAULT_EMAIL;
        else $this->defaultFrom = 'test@test.com';
        
        if (!empty($template)) $this->template = $template;
    }

    /**
     *
     * function: revertTmp
     * Revert templating
     * @access public
     * @return Templater
     */
    public function revertTmp() {
        if (!empty($this->tmpPrior) && !empty($this->tmp)) $this->tmp->setTemplate($this->tmpPrior);
    }

    /**
     *
     * function: prepareTmp
     * Prepare templating
     * @access public
     * @return Templater
     */
    public function prepareTmp() {
        $this->tmp = Templater::init();
        $this->tmpPrior = $this->tmp->currentTemplate();
    }

    /**
     *
     * function: tmp
     * Handles templating
     * @access public
     * @return Templater
     */
    public function tmp($tmp=false) {
        if (!is_object($this->tmp)) $this->prepareTmp();
        if (!empty($tmp)) $this->tmp = $tmp;
        
        $this->tmp->setTemplate($this->tmp);
        return $this->tmp;
    }

    /**
     *
     * function: send
     * Queues an e-mail for sending
     * @access public
     * @param string $to
     * @param string $subject
     * @param string $content
     * @param string $from (optional)
     * @return boolean
     */
    public function send($to,$subject,$content,$from=false) {
        $this->revertTmp();
        if (!$from) $from = $this->defaultFrom;
        
        $sql = 'INSERT INTO email_queue (recipient,sender,subject,content) VALUES (?,?,?,?);';

        $db = new DB();
        $db->connect();
        return $db->insert($sql,array($to,$from,$subject,$content));
    }
    
    /**
     *
     * function: top
     * Gathers the default email header
     * @access public
     * @return string
     */
    public function top() {
        $this->tmp()->setVar('header','title',DEFAULT_TITLE);
        return $this->tmp()->render('header');
    }

    /**
     *
     * function: signature
     * Gathers the default email footer
     * @access public
     * @return string
     */
    public function signature() {
        $this->tmp()->setVar('footer','email',DEFAULT_EMAIL);
        $this->tmp()->setVar('footer','phone',DEFAULT_PHONE);
        $this->tmp()->setVar('footer','title',DEFAULT_TITLE);
        $this->tmp()->setVar('footer','uri',DEFAULT_URI);
        return $this->tmp()->render('footer');
    }

    /**
     *
     * function: build
     * Builds a complete e-mail, with default header and footer
     * @access public
     * @param string $content
     * @return string
     */
    public function build($content) {
        $this->tmp()->setVar('default','header',$this->top());
        $this->tmp()->setVar('default','content',$content);
        $this->tmp()->setVar('default','footer',$this->signature());
        return $this->tmp()->render('default');
    }

    /**
     *
     * function: testMsg
     * Sends a test message to the default email address
     * @access public
     * @param string $to (optional)
     * @return boolean
     */
    public function testMsg($to=false) {
        if (empty($to)) $to = $this->defaultFrom;
        return $this->send($to,'Test',$this->build('This is a test... This is only a test...'),false);
    }

    /**
     *
     * function: sendCustom
     * Sends a custom message
     * @access public
     * @param string $to
     * @param string $subject
     * @param string $content
     * @return boolean
     */
    public function sendCustom($to,$subject,$content) {
        return $this->send($to,$subject,$this->build($content),false);
    }
}


