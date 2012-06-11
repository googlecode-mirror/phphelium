<?php

session_start();

// debug stuff...
if (!empty($_SESSION['system']['DEBUG'])) $timeStart = microtime(true);

require_once('prepare.php');
require_once('configure.php');
require_once('load.php');

// debug stuff...
if (!empty($_SESSION['system']['DEBUG'])) require_once('debug.php');