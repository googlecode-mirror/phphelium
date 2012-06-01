<?php

/*
 * Pages.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Model for Pages data objects
 */

class Pages extends Model {
    protected $class = 'Pages';
    protected $table = 'pages';
    protected $primary = 'page_id';

    public $validate = array(
        'title'=>array('filled',true),
        'uri'=>array('filled',true),
        'controller'=>array('filled',true)
    );

    public $schema = array(
        'page_id'=>array('type'=>'int(10)','isNull'=>false,'isPrimary'=>true),
        'title'=>array('type'=>'varchar(100)','isNull'=>true),
        'keywords'=>array('type'=>'varchar(75)','isNull'=>true),
        'description'=>array('type'=>'varchar(150)','isNull'=>true),
        'summary'=>array('type'=>'varchar(150)','isNull'=>true),
        'base'=>array('type'=>'varchar(50)','isNull'=>true),
        'uri'=>array('type'=>'text','isNull'=>false),
        'controller'=>array('type'=>'varchar(30)','isNull'=>false),
        'directive'=>array('type'=>'varchar(30)','isNull'=>true),
        'secure'=>array('type'=>'tinyint(1)','isNull'=>false,'defaultValue'=>'0'),
        'params'=>array('type'=>'text','isNull'=>true),
        'bypass'=>array('type'=>'tinyint(1)','isNull'=>false,'defaultValue'=>'0'),
        'matching'=>array('type'=>'tinyint(1)','isNull'=>false,'defaultValue'=>'0'),
        'cache'=>array('type'=>'tinyint(1)','isNull'=>false,'defaultValue'=>'0')
    );

    function __construct() {}    
}


