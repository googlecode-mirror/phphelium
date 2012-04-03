<?php

/*
 * buildModel.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: creates tables from provided model schema
 *          can only be run from the command line
 */

require_once('../src/prepare.php');

$model = $argv[1];
require_once('../src/models/'.$model.'.php');

$model = new $model();
if (!empty($argv[2])) $model->point('schemeBuild',$argv[2]);

if ($model->buildSchema()) {
    echo('The model has been built');
} else {
    echo('The model failed to build');
}