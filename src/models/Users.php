<?php

/*
 * Users.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Model for Users data objects
 */

class Users extends Model {
    protected $class = 'Users';
    protected $table = 'users';
    protected $primary = 'user_id';

    public $validate = array(
        'username'=>array('filled',true),
        'password'=>array('filled',true),
        'first_name'=>array('filled',true),
        'last_name'=>array('filled',true),
        'email'=>array('filled',true),
        'language'=>array('filled',true)
    );

    public $schema = array(
        'user_id'=>array('type'=>'int(10)','isNull'=>false,'isPrimary'=>true),
        'username'=>array('type'=>'varchar(100)','isNull'=>true),
        'password'=>array('type'=>'varchar(100)','isNull'=>true),
        'first_name'=>array('type'=>'varchar(50)','isNull'=>true),
        'last_name'=>array('type'=>'varchar(50)','isNull'=>false),
        'email'=>array('type'=>'varchar(50)','isNull'=>false),
        'language'=>array('type'=>'varchar(2)','isNull'=>false),
        'is_admin'=>array('type'=>'tinyint(1)','isNull'=>false,'defaultValue'=>'0'),
        'is_active'=>array('type'=>'tinyint(1)','isNull'=>false,'defaultValue'=>'1'),
        'create_date'=>array('type'=>'timestamp','isNull'=>false,'defaultValue'=>'CURRENT_TIMESTAMP')
    );

    function __construct() {}

    /**
     *
     * function: login
     * Used for handling all login logic for incoming users
     * @access public
     * @param string $username
     * @param string $password
     * @param boolean $sticky (optional)
     * @return [boolean,User]
     */
    public function login($username,$password,$sticky=false) {
        $sql = 'SELECT '.$this->primary.' FROM '.$this->table.' WHERE username = ? AND password = ? AND is_active = 1;';
        $results = $this->getOne($sql,array($username,$password));
        if (empty($results)) return false;
        else {
            $user = new Users();
            $user->load($results);

            if (!empty($sticky)) Cookie::setCookie('user',$user->user_id);
            
            return $user;
        }
    }

    /**
     *
     * function: loginAPI
     * Used for handling all login logic for incoming API requests
     * @access public
     * @param string $username
     * @param string $key
     * @param string $shared
     * @return [boolean,User]
     */
    public function loginAPI($username,$key,$shared) {
        if ($shared <> SHARED_API_KEY) return false;
        else {
            $sql = 'SELECT '.$this->primary.' FROM '.$this->table.' WHERE username = ? AND MD5(CONCAT(username,password)) = ? AND is_active = 1;';
            $results = $this->getOne($sql,array($username,$key));
            if (empty($results)) return false;
            else {
                $user = new Users();
                $user->load($results);
                return $user;
            }
        }
    }

    /**
     *
     * function: isAdmin
     * Used for checking is active user is an administrator
     * @access public
     * @return boolean
     */
    public function isAdmin() {
        if (empty($this->user_id)) return false;
        elseif (empty($this->is_admin)) return false;
        else return true;
    }
}

?>