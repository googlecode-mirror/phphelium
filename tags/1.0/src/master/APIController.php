<?php

/*
 * APIController.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Master controller for API behavior
 */

abstract class APIController extends Component {
    protected $class;
    protected $template;
    private $errors;
    private $reqData;
    private $apiRoutes = array();

    function __construct($merge=false) {
        $this->reqData = parent::getRequest($merge);
        $user = Users::login($this->reqData['username'],$this->reqData['key']);

        if (empty($user)) {
            $this->tmp('error_xml')->setVar('errors',array((object)array('id' => EID_BAD_LOGIN,
                                                                         'message' => 'This is not a valid API user')));
            
            exit($this->tmp('error_xml')->render('error',true));
        }
    }
}

?>