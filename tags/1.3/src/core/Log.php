<?php

/*
 * Log.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all logging
 */

class Log extends DB {
    protected $dbo = 'file';
    protected $baseLocation = LOG_LOCATION;
    protected $writeFile = 'default.log';
    protected $dieOnError = false;
    protected $defaultPerm = 0777;
    protected $onlyMock = false;

    function __construct($build=null,$resource=null) {
        if (!empty($build)) {
            if (!empty($build['dbo'])) $this->setDBO($build['dbo']);
            if (!empty($build['base'])) $this->setBaseLocation($build['base']);
            if (!empty($build['writeFile'])) $this->setWriteFile($build['writeFile']);
            if (!empty($build['dieOn'])) $this->setDieOnError($build['dieOn']);
            if (!empty($build['perm'])) $this->setDefaultPerm($build['perm']);
            if (!empty($build['mockOnly'])) $this->onlyMock = true;
        }

        if ($this->dbo !== 'db') {
            if (!is_dir($this->baseLocation)) mkdir($this->baseLocation,$this->defaultPerm,true);
        }
    }

    /**
     *
     * function: exeFail
     * What to do on failure
     * @access public
     * @param string $msg
     * @return [boolean,Error]
     */
    private function exeFail($msg) {
        if ($this->dieOnError == true) {
            throw new Exception($msg);
        } else return false;
    }

    /**
     *
     * function: add
     * Writes a log as set
     * @access public
     * @param string $scale
     * @param string $data
     * @return [boolean,Error]
     */
    private function add($scale,$data) {
        if ($this->onlyMock) return true;
        elseif ($this->dbo == "db") {
            return $this->insert('INSERT INTO ? ("error_date","host","remote_host","scale","info")
                                  VALUES (NOW(),?,?,?,?);',
                                      array($this->writeFile,
                                            (!empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '-'),
                                            (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-'),
                                            $scale,
                                            $data));

        } else return $this->appendLog($scale,$data);
    }

    /**
     *
     * function: appendLog
     * Writes a log to a log file
     * @access public
     * @param string $scale
     * @param string $data
     * @return [boolean,Error]
     */
    private function appendLog($scale,$data) {
        $f = fopen($this->baseLocation.$this->writeFile,'a');
        if (is_resource($f)) {
            $append =  sprintf(date("Y-m-d H:i:s").'%c',9);
            $append .= sprintf((string)(!empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '-').'%c',9);
            $append .= sprintf((string)(!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-').'%c',9);
            $append .= sprintf($scale.'%c',9);
            $append .= sprintf($data."%c \n ",13);

            if (fwrite($f,$append)) return true;
            else return $this->exeFail("Write failed");
        } else return $this->exeFail("File not found");
        flose($f);
    }

    /**
     *
     * function: setDefaultPerm
     * Sets the default permission setting
     * @access public
     * @param string $perm
     * @return boolean
     */
    public function setDefaultPerm($perm) {
        $this->defaultPerm = $perm;

        return true;
    }

    /**
     *
     * function: setDBO
     * Sets the default DBO setting
     * @access public
     * @param string $dbo
     * @return boolean
     */
    public function setDBO($dbo) {
        if (!in_array($build[0],array("file","db"))) throw new Exception("Bad DBO");
        else $this->dbo = $dbo;

        return true;
    }

    /**
     *
     * function: setBaseLocation
     * Sets the default base location setting
     * @access public
     * @param string $baseLocation
     * @return boolean
     */
    public function setBaseLocation($baseLocation) {
        $this->baseLocation = $baseLocation;

        return true;
    }

    /**
     *
     * function: setWriteFile
     * Sets the default file setting
     * @access public
     * @param string $writeFile
     * @return boolean
     */
    public function setWriteFile($writeFile) {
        $this->writeFile = $writeFile;

        return true;
    }

    /**
     *
     * function: setDieOnError
     * Sets the default die behavior setting
     * @access public
     * @param string $doe
     * @return boolean
     */
    public function setDieOnError($doe) {
        if ($doe == true) $this->dieOnError = true;
        else $this->dieOnError = false;

        return true;
    }

    /**
     *
     * function: error
     * Sends an error to the log
     * @access public
     * @param string $data
     * @return boolean
     */
    public function error($data) {
        return $this->add("error",$data);
    }

    /**
     *
     * function: warning
     * Sends an warning to the log
     * @access public
     * @param string $data
     * @return boolean
     */
    public function warning($data) {
        return $this->add("warning",$data);
    }

    /**
     *
     * function: info
     * Sends an info to the log
     * @access public
     * @param string $data
     * @return boolean
     */
    public function info($data) {
        return $this->add("info",$data);
    }
}


