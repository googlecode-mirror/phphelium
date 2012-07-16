<?php namespace Helium;

/*
 * APIController.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Master controller for API behavior
 */

abstract class APIController extends Component {
    protected $class;
    protected $template;
    public $reqData, $err;
    
    function __construct($merge=false) {
        $this->reqData = parent::getRequest($merge);
        $this->err = false;
        
        if (empty($this->reqData['username']) ||
            empty($this->reqData['key'])) {
                $this->tmp('error_xml')->setVar('errors',array((object)array('id' => (defined('EID_BAD_DATA') ? EID_BAD_DATA : 0),
                                                                             'message' => 'You are missing required data')));

                exit($this->tmp('error_xml')->render('error',true));
        } else {
            $user = Users::login($this->reqData['username'],$this->reqData['key']);
            if (empty($user)) {
                $this->tmp('error_xml')->setVar('errors',array((object)array('id' => (defined('EID_BAD_LOGIN') ? EID_BAD_LOGIN : 0),
                                                                             'message' => 'This is not a valid API user')));

                exit($this->tmp('error_xml')->render('error',true));
            }
        }
    }
}

