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
        'username'=>array('type'=>'varchar(100)','isNull'=>false),
        'password'=>array('type'=>'varchar(100)','isNull'=>false),
        'first_name'=>array('type'=>'varchar(50)','isNull'=>true),
        'last_name'=>array('type'=>'varchar(50)','isNull'=>true),
        'email'=>array('type'=>'varchar(50)','isNull'=>true),
        'language'=>array('type'=>'varchar(2)','isNull'=>false,'defaultValue'=>'en'),
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
     * function: register
     * Register a new user
     * @access public
     * @param string $username
     * @param string $password
     * @param boolean $sticky (optional)
     * @return [boolean,User]
     */
    public function register($username,$password,$info=false) {
        $sql = 'INSERT INTO '.$this->table.' (username,password) VALUES (?,?);';
        $userId = $this->insert($sql,array($username,$password));
        if (empty($userId)) return false;
        else {
            if (!empty($info)) Users::updateUser($info,$userId);

            Session::setUser($userId);
            if (!empty($sticky)) Cookie::setCookie('user',$userId);
            
            return $userId;
        }
    }

    /**
     *
     * function: updateUser
     * Register a new user
     * @access public
     * @param string $username
     * @param string $password
     * @param boolean $sticky (optional)
     * @return [boolean,User]
     */
    public function updateUser($info,$userId=false) {
        $els = array(); $opts = array();
        foreach($info as $el => $var) { $els[] = $el.' = ?'; $opts[] = $var; }
        $opts[] = $userId;

        $sql = 'UPDATE '.$this->table.' SET '.implode(', ',$els).' WHERE '.$this->primary.' = ?;';
        $this->update($sql,$opts);

        $this->clear($user);
        return $userId;
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