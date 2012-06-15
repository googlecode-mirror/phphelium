<?php

session_start();

$root = str_replace('/html','',realpath(dirname(__FILE__)));

// debug stuff...
if (!empty($_SESSION['system']['DEBUG'])) $timeStart = microtime(true);
ini_set('display_errors',1);
require_once($root.'/system/prepare.php');
require_once($root.'/system/configure.php');
require_once($root.'/system/load.php');

// debug stuff...
if (!empty($_SESSION['system']['DEBUG'])) require_once($root.'/system/debug.php');