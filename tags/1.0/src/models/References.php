<?php

/*
 * References.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Model for References data objects
 */

class References extends Model {
    protected $class = 'References';
    protected $table = 'ref_options';
    protected $primary = 'ref_type_id';

    public $validate = array(
        'ref_type'=>array('filled',true)
    );

    public $schema = array(
        'ref_type_id'=>array('type'=>'int(10)','isNull'=>false,'isPrimary'=>true),
        'ref_type'=>array('type'=>'varchar(100)','isNull'=>true)
    );

    function __construct() {}
}

?>
