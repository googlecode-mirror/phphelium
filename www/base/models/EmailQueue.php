<?php namespace Helium;

/*
 * EmailQueue.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Model for EmailQueue data objects
 */

class EmailQueue extends Model {
    protected $class = 'EmailQueue';
    protected $table = 'email_queue';
    protected $primary = 'email_queue_id';

    public $validate = array(
        'recipient'=>array('filled',true),
        'sender'=>array('filled',true),
        'subject'=>array('filled',true),
        'content'=>array('filled',true)
    );

    public $schema = array(
        'email_queue_id'=>array('type'=>'int(10)','isNull'=>false,'isPrimary'=>true),
        'recipient'=>array('type'=>'varchar(100)','isNull'=>true),
        'sender'=>array('type'=>'varchar(100)','isNull'=>true),
        'subject'=>array('type'=>'varchar(250)','isNull'=>true),
        'content'=>array('type'=>'text','isNull'=>false),
        'sent_date'=>array('type'=>'datetime','isNull'=>false),
        'is_active'=>array('type'=>'tinyint(1)','isNull'=>false,'defaultValue'=>'1'),
        'create_date'=>array('type'=>'timestamp','isNull'=>false,'defaultValue'=>'CURRENT_TIMESTAMP')
    );

    function __construct() {
        // nothing to do here...
    }
}

