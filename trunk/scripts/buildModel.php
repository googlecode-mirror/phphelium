<?php

/*
 * buildModel.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: creates tables from provided model schema
 *          can only be run from the command line
 */

$ini = parse_ini_file('../settings.ini',true);
define('ROOT',$ini['system']['root']);
define('SRC',$ini['system']['src']);
define('MASTER_DB_STRING',$ini['database']['master']);
define('SLAVE_DB_STRING',$ini['database']['slave']);
define('MEMCACHE_SERVERS',$ini['memcache']['serverList']);
define('MEMCACHE_DEFAULT_EXPIRY',$ini['memcache']['defaultExpiry']);

require_once('../src/core/DB.php');
require_once('../src/core/Model.php');

$model = $argv[1];
require_once('../src/models/'.$model.'.php');

$model = new $model();
if (!empty($argv[2])) $model->point('schemeBuild',$argv[2]);

if ($model->buildSchema()) {
    echo('The model has been built');
} else {
    echo('The model failed to build');
}

?>