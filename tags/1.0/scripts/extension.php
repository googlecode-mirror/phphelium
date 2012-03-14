<?php

ini_set('allow_url_fopen',1);

/*
 * buildModel.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: creates tables from provided model schema
 *          can only be run from the command line
 */

require_once('../src/prepare.php');

$extension = $argv[1];
if (!empty($extension)) {
    $process = new Process();
    $results = $process->service('http://www.phphelium.com/get-package-uri/','extension='.$extension,'GET');
    if (!empty($results)) {
        $results = json_decode($results);
        $extension = $results->id;
        $uri = $results->uri;

        if (!file_exists(SRC.'utilities/'.$extension)) {
            $extDir = SRC.'utilities/'.$extension.'tmp/';
            $data = file_get_contents($uri);

            mkdir($extDir,0777);

            $file = fopen($extDir.$extension.'.tar.gz','w+');
            fputs($file,$data);
            chmod($extDir.$extension.'.tar.gz',0777);
            fclose($file);

            $cmd = 'bash '.ROOT.'scripts/extractor.sh '.$extension;
            $result = shell_exec($cmd);
            echo 'Extension has been added!';
        } else echo 'Extension already exists';
    } else echo 'Failed to get repo data';
} else echo 'Did not provide extension name';

?>