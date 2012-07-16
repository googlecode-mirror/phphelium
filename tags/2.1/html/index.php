<?php

session_start();

$rdir = str_replace('/html','',realpath(dirname(__FILE__)));

// debug stuff...
if (!empty($_SESSION['system']['DEBUG'])) $timeStart = microtime(true);

require_once($rdir.'/system/prepare.php');
require_once($rdir.'/system/configure.php');
require_once($rdir.'/system/load.php');

// debug stuff...
if (!empty($_SESSION['system']['DEBUG'])) require_once($rdir.'/system/debug.php');
