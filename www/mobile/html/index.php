<?php

session_start();

$rdir = false;
if (file_exists('../bootstrap.ini') == true) {
    $ini = parse_ini_file('../bootstrap.ini',true);
    if (!empty($ini['core'])) {
        $rdir = $ini['core'];
    }
}

if (!empty($rdir)) {
    $_SESSION['system[coreLoc]'] = $ini['core'];
    if (!empty($ini['site'])) $_SESSION['system[siteDir]'] = $ini['site'];
    if (!empty($ini['precedence'])) $_SESSION['system[sitePrecede]'] = $ini['precedence'];

    // debug stuff...
    if (!empty($_SESSION['system']['DEBUG'])) $timeStart = microtime(true);

    require_once($rdir.'/system/prepare.php');
    require_once($rdir.'/system/configure.php');
    require_once($rdir.'/system/load.php');

    // debug stuff...
    if (!empty($_SESSION['system']['DEBUG'])) require_once($rdir.'/system/debug.php');
} else throw new Exception('Failure to initialize');