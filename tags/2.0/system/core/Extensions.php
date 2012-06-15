<?php
namespace Helium;

/*
 * Extensions.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Contain all extension behavior
 */

class Extensions {
    private $extensionSet = array();

    function __construct($extensionSet=false) {
        if (!empty($extensionSet)) $this->extensionSet = $extensionSet;
    }

    /**
     *
     * function: load
     * Load a extension
     * @access public
     * @return [varied]
     */
    function load($extension=false,$cmd=true) {
        if (!empty($extension)) {
            if (!file_exists(SRC.'utilities/'.$extension)) {
                $process = new Process();
                $results = $process->service('http://www.phphelium.com/get-package-uri/','extension='.$extension,'GET');
                if (!empty($results)) {
                    $results = json_decode($results);
                    $extension = $results->id;
                    $uri = $results->uri;

                    $extDir = SRC.'utilities/'.$extension.'tmp/';
                    $data = file_get_contents($uri);

                    mkdir($extDir,0777);

                    $file = fopen($extDir.$extension.'.tar.gz','w+');
                    fputs($file,$data);
                    chmod($extDir.$extension.'.tar.gz',0777);
                    fclose($file);

                    $cmd = 'bash '.ROOT.'scripts/extractor.sh '.$extension.(empty($cmd) ? ' '.ROOT : '');
                    $result = shell_exec($cmd);

                    return 'Extension has been added!';
                } else return 'Failed to get repo data';
            } else return 'Extension already exists';
        } else return false;
    }


    /**
     *
     * function: loadAll
     * Load all extensions
     * @access public
     * @return [varied]
     */
    function loadAll($extensionSet=false) {
        if (!empty($extensionSet)) $this->extensionSet = $extensionSet;
        foreach($this->extensionSet as $extension) $this->load($extension);
    }
}