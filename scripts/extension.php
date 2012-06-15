<?php

ini_set('allow_url_fopen',1);

/*
 * buildModel.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: creates tables from provided model schema
 *          can only be run from the command line
 */

require_once('../system/prepare.php');

$extension = (!empty($argv[1]) ? $argv[1] : (!empty($_REQUEST['package']) ? $_REQUEST['package'] : false));
if (!empty($extension)) {
    $ext = new Extensions();
    echo $ext->load($extension);
} else echo 'Did not provide extension name';