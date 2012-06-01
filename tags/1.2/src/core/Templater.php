<?php

ini_set('pcre.backtrack_limit',1000000000);

/*
 * Templater.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all templating functionality
 */

class Templater {
    private static $instance;
    private $root, $template, $chunk, $map, $output, $hide, $data, $inherit, $cache;

    function __construct($root=DEFAULT_TEMPLATE_ROOT) {
        $this->root = $root;
        $this->chunk = array();
        $this->map = array();
        $this->output = array();
        $this->hide = array();
        $this->data = array();
        $this->inherit = array();
        $this->cache = array();
    }

    /**
     *
     * function: init
     * Prepares the templater for work
     * @access public
     * @param string $tmp (optional)
     * @return Templater
     */
    public static function init($tmp=null) {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        if (!empty($tmp)) self::$instance->setTemplate($tmp);
        return self::$instance;
    }

    /**
     *
     * function: setRoot
     * Sets the root of the template folder it's working with
     * @access public
     * @param string $dir (optional)
     * @return null
     */
    public function setRoot($dir = '') {
        if (empty($dir)) $dir = SRC.'/templates/';
        if (!@is_dir($dir)) throw new Error('Template directory does not exist.');

        if (substr($dir,-1) != '/') $dir .= '/';

        $this->root = $dir;
    }

    /**
     *
     * function: determineRoot
     * Determines the best possible root directory
     * @access public
     * @return string
     */
    public function determineRoot() {
        if (!empty($_SERVER['SERVER_NAME'])) {
            if (defined('DEFAULT_URI') && $_SERVER['SERVER_NAME'] <> DEFAULT_URI && substr_count($_SERVER['SERVER_NAME'],DEFAULT_URI)) $subChk = str_replace('.'.DEFAULT_URI,'',$_SERVER['SERVER_NAME']);
            else $subChk = $_SERVER['SERVER_NAME'];
            
            $tmp = ROOT.'custom/'.$subChk.'/templates/';
            if (file_exists($tmp)) $this->setRoot($tmp);
            else $this->setRoot(SRC.'/templates/');
        } else $this->setRoot(SRC.'/templates/');

        return $this->getRoot();
    }

    /**
     *
     * function: getRoot
     * Gets the current root directory
     * @access public
     * @return [string,boolean]
     */
    public function getRoot() {
        return (!empty($this->root) ? $this->root : false);
    }

    /**
     *
     * function: setTemplate
     * Sets the active template to be worked with
     * @access public
     * @param string $template
     * @param string $dir (optional)
     * @param string $cache (optional)
     * @return null
     */
    public function setTemplate($template,$dir=false,$cache=false) {
        // Load language specific constants for working template before proceeding
        $language = new Language();
        $language->loadLanguage($template);

        if (!substr_count($template,'.tmp')) $template .= '.tmp';
        if ($dir !== false) $this->setRoot($dir);
        else $this->determineRoot();

        $filepath = $this->root.$template;

        if (!@file_exists($filepath)) {
            if (file_exists(SRC.'/templates/'.$template)) {
                $this->setRoot(SRC.'/templates/');
                $filepath = $this->root.$template;
            } else throw new Error('Template does not exist');
        }

        if ($cache === true && !key_exists($filepath, $this->cache)) $this->cache[$filepath] = @file_get_contents($filepath);

        $this->template = $template;
    }

    /**
     *
     * function: getTemplate
     * Gets the current template
     * @access public
     * @return string
     */
    public function getTemplate() {
        if (!isset($this->template)) throw new Error('Template does not exist');
        return $this->template;
    }

    /**
     *
     * function: currentTemplate
     * Determines the current template
     * @access public
     * @return string
     */
    public function currentTemplate() {
        if ($this->template) return $this->template;
        else return false;
    }

    /**
     *
     * function: initChunk
     * Load a chunk into memory
     * @access public
     * @param string $chunk
     * @return null
     */
    private function initChunk($chunk) {
        if (!isset($this->data[$chunk])) $this->data[$chunk][] = array();
    }

    /**
     *
     * function: setVar
     * Sets a variable within a chunk
     * @access public
     * @param string $chunk
     * @param string $data
     * @param string $value
     * @param boolean $append (optional)
     * @return null
     */
    public function setVar($chunk,$data,$value='',$append=false) {
        $arr = (@is_array($chunk)) ? $chunk : array($chunk);

        foreach($arr as $chunk) {
            $this->initChunk($chunk);

            if (@is_string($data)) $this->addVar($chunk,$data,$value,$append);
            elseif (@is_array($data)) $this->addArray($chunk,$data,$append);
            elseif (@is_object($data)) $this->addObject($chunk,$data,$append);
        }
    }

    /**
     *
     * function: appendData
     * Appends a variable within a chunk
     * @access public
     * @param string $chunk
     * @param string $data
     * @param string $value
     * @return null
     */
    public function appendData($chunk,$data,$value='') {
        $this->setVar($chunk,$data,$value,true);
    }

    /**
     *
     * function: setDefault
     * Sets the default chunk
     * @access public
     * @param string $chunk
     * @param string $key
     * @param string $value
     * @return null
     */
    public function setDefault($chunk,$key,$value='') {
        if (!isset($this->data['DEFAULT'])) $this->data['DEFAULT'] = array();
        $this->data['DEFAULT'][$chunk] = array($key => $value);
    }

    /**
     *
     * function: getDefault
     * Gets the default chunk
     * @access public
     * @param string $chunk
     * @param string $key
     * @return null
     */
    public function getDefault($chunk,$key='') {
        if (!empty($key)) {
            return (isset($this->data['DEFAULT'][$chunk][$key])) ? $this->data['DEFAULT'][$chunk][$key] : '';
        } else return (isset($this->data['DEFAULT'][$chunk])) ? $this->data['DEFAULT'][$chunk] : array();
    }

    /**
     *
     * function: setGlobal
     * Sets a global variable
     * @access public
     * @param string $data
     * @param string $value
     * @return null
     */
    public function setGlobal($data,$value='') {
        $this->setVar('GLOBAL',$data,$value);
    }

    /**
     *
     * function: setGlobals
     * Sets an array of global variables
     * @access public
     * @param array $data
     * @return null
     */
    public function setGlobals($data) {
        foreach ($data as $key => $val) $this->setGlobal($key,$val);
    }

    /**
     *
     * function: getGlobal
     * Gets a global variable
     * @access public
     * @param string $key
     * @return string
     */
    public function getGlobal($key) {
        return array_key_exists($key,$this->data['GLOBAL'][0]) ? $this->data['GLOBAL'][0][$key] : '';
    }

    /**
     *
     * function: addVar
     * Adds a string to a chunk
     * @access public
     * @param string $chunk
     * @param string $key
     * @param string $value
     * @param boolean $append (optional)
     * @return null
     */
    private function addVar($chunk,$key,$value,$append=false) {
        if (count($this->data[$chunk]) < 1) $this->data[$chunk][0][$key] = $value;
        for ($i = 0; $i < count($this->data[$chunk]); $i++) $this->setValue($chunk,$i,$key,$value,$append);
    }

    /**
     *
     * function: addArray
     * Adds an array of objects to a chunk for looping
     * @access public
     * @param string $chunk
     * @param array $data
     * @param boolean $append (optional)
     * @return null
     */
    private function addArray($chunk,$data,$append=false) {
        if (!key_exists('0',$data)) return $this->addObject($chunk,$data);
        for ($i = 0; $i < count($data); $i++) foreach ($data[$i] as $key => $value) $this->setValue($chunk,$i,$key,$value,$append);
    }

    /**
     *
     * function: addObject
     * Adds an object to a chunk for mapping
     * @access public
     * @param string $chunk
     * @param object $data
     * @param boolean $append (optional)
     * @return null
     */
    private function addObject($chunk,$data,$append=false) {
        foreach ($data as $key => $value) $this->addVar($chunk,$key,$value,$append);
    }

    /**
     *
     * function: addResult
     * Adds an SQL result to a chunk for mapping
     * @access public
     * @param string $chunk
     * @param array $result
     * @param boolean $append (optional)
     * @return null
     */
    public function addResult($chunk,$result,$append=false) {
        $data = array();
        while($row = mysql_fetch_assoc($result)) $data[] = $row;
        $this->addArray($chunk,$data,$append);
    }

    /**
     *
     * function: setValue
     * Sets the variable added into memory
     * @access public
     * @param string $chunk
     * @param string $index
     * @param string $key
     * @param string $value
     * @param boolean $append
     * @return null
     */
    private function setValue($chunk,$index,$key,$value,$append) {
        if ($append === true) {
            if (!isset($this->data[$chunk][$index][$key])) $this->data[$chunk][$index][$key] = '';
            $this->data[$chunk][$index][$key] .= $value;
        } else $this->data[$chunk][$index][$key] = $value;
    }

    /**
     *
     * function: purge
     * Purges a chunk out of memory
     * @access public
     * @param string $chunk
     * @return null
     */
    public function purge($chunk='') {
        if (!empty($chunk)) {
            unset($this->data[$chunk]);
            unset($this->output[$chunk]);
        } else {
            unset($this->data);
            unset($this->output);
        }

        unset($this->hide[$chunk]);
    }

    /**
     *
     * function: clear
     * Clears a chunk of set variables
     * @access public
     * @param string $chunk
     * @return null
     */
    public function clear($chunk='') {
        if (!empty($chunk)) unset($this->output[$chunk]);
        else unset($this->output);
    }

    /**
     *
     * function: hide
     * Hides a particular chunk
     * @access public
     * @param string $chunk
     * @return null
     */
    public function hide($chunk='') {
        $this->hide[$chunk] = $chunk;
    }

    /**
     *
     * function: hideAll
     * Hides an array of chunks chunk
     * @access public
     * @param array $chunks
     * @return null
     */
    public function hideAll($chunks=array()) {
        foreach($chunks as $chunk) $this->hide($chunk);
    }

    /**
     *
     * function: unhide
     * Unhides a particular chunk
     * @access public
     * @param string $chunk
     * @return null
     */
    public function unhide($chunk = '') {
        unset($this->hide[$chunk]);
    }

    /**
     *
     * function: unhideAll
     * Unhides an array of chunks chunk
     * @access public
     * @param array $chunks
     * @return null
     */
    public function unhideAll($chunks=array()) {
        foreach($chunks as $chunk) $this->unhide($chunk);
    }

    /**
     *
     * function: hasData
     * Determines if a chunk has set data
     * @access public
     * @param string $chunk
     * @return boolean
     */
    public function hasData($chunk) {
        return (isset($this->data[$chunk]) && !empty($this->data[$chunk]));
    }

    /**
     *
     * function: setMap
     * Sets a chunk in the data map
     * @access public
     * @param string $chunk
     * @param string $key
     * @param array $keys
     * @return null
     */
    public function setMap($chunk,$key="",$keys = array()) {
        $this->map[$chunk] = array('from' => $key,
                                   'to' => $keys);
    }

    /**
     *
     * function: render
     * Maps data to the chunk and output the result as a string
     * @access public
     * @param string $chunk
     * @param boolean $purge (optional)
     * @return string
     */
    public function render($chunk=null,$purge=false) {
        if (empty($chunk)) {
            $chunk = str_replace('.tmp','',$this->template);
            $purge = true;
        }

        $template = $this->template;
        $filepath = $this->root.$template;

        if (key_exists($filepath, $this->cache)) $this->template = $this->cache[$filepath];
        else {
            $this->template = @file_get_contents($filepath);
            $this->cache[$filepath] = $this->template;
        }

        $output = $this->mapTemplate($chunk);
        if ($purge === true) $this->purge($chunk);

        $this->setTemplate($template);

        return $output;
    }

    /**
     *
     * function: getAttributes
     * Gets attributes of a specific chunk
     * @access public
     * @param string $chunk
     * @param array $matches (optional)
     * @return array
     */
    private function getAttributes($chunk,$matches=array()) {
        $attributes = array();
        if (!isset($matches[1])) preg_match("/(?<=\<tmp:".$chunk.")(.*?)(?=\>)/s",$template,$matches);
        if (!isset($matches[0])) return $attributes;
        $atts = (isset($matches[1])) ? $matches[1] : $matches[0];
        $arr = explode(' ',strtolower($atts));

        for ($i = 0; $i < count($arr); $i++) {
            $att = explode('=', str_replace('"', '', $arr[$i]));
            $attributes[$att[0]] = isset($att[1]) ? $att[1] : '';
        }

        if (!isset($attributes['type'])) $attributes['type'] = '';

        return $attributes;
    }

    /**
     *
     * function: extractChunk
     * Gets a chunk from the template by the tmp tag
     * @access public
     * @param string $chunk (optional)
     * @return string
     */
    private function extractChunk($chunk=null) {
        if ($chunk == null) return $this->template;
        preg_match("/\<tmp:".$chunk."(.*?)\>(.*?)\<\/tmp:".$chunk."\>/s", $this->template, $matches);
        return (isset($matches[0])) ? $matches[0] : "";
    }

    /**
     *
     * function: parseChunk
     * Processing mapping of data to a chunk by operators
     * @access public
     * @param string $chunk
     * @param int $ct (optional)
     * @param int $i (optional)
     * @return string
     */
    private function parseChunk($chunk,$ct=0,$i=0) {
        preg_match("/\<tmp:".$chunk."(.*?)\>(.*?)\<\/tmp:".$chunk."\>/s", $this->template, $matches);
        if (!isset($matches[0])) return;

        $attributes = $this->getAttributes($chunk,$matches);
        $template = preg_replace("/\<(\/)?tmp:".$chunk."(.*?)\>/", "",$matches[0]);

        switch($attributes['type']) {
            case 'oddeven':
                $sub = (($i + 1) % 2 == 0) ? 'even' : 'odd';

            break;

            case 'firstlast':
                if ($i == 0) $sub = 'first';
                elseif ($i == ($ct - 1)) $sub = 'last';
                else $sub = '';

            break;

            case 'condition':
                $var = $attributes['var'];
                if (isset($var) && isset($this->data[$chunk][$i][$var])) $sub = $this->data[$chunk][$i][$var];
                elseif (isset($var) && isset($this->data['GLOBAL'][0][$var])) $sub = $this->data['GLOBAL'][0][$var];
                else $sub = '';

            break;
        }

        if (isset($sub)) {
            preg_match("/\<sub:".$chunk."(\[?)$sub(\]?)\>(.*?)\<\/sub:".$chunk."\>/si", $this->template, $matches);
            if (empty($matches[0])) preg_match("/\<sub:".$chunk."\>(.*?)\<\/sub:".$chunk."\>/si", $this->template, $matches);
            if (!isset($matches[0]) || empty($matches[0])) $matches[0] = "";
            $template = preg_replace("/\<(\/)?sub:".$chunk."(.*?)\>/", "", $matches[0]);
        }

        unset($matches);
        unset($attributes);

        return $template;
    }

    /**
     *
     * function: parseData
     * Maps all chunks in a map to their data
     * @access public
     * @param string $chunk
     * @param string $template
     * @param array $keys
     * @param string $data
     * @param int $ct
     * @param int $i
     * @return string
     */
    private function parseData($chunk,$template,$keys,$data,$ct,$i) {
        foreach ($keys as $key => $value) {
            $value = (key_exists($key,$data)) ? $data[$key] : "";

            if (!empty($value) || $value === "0") {
                if (isset($this->map[$chunk]) && in_array($key, $this->map[$chunk]['to'])) {
                    $template = str_replace("{\$".$this->map[$chunk]['from']."}",
                                                                    "{\$".$key."}",
                                                                    $this->parseChunk($chunk,$ct,$i));

                    $this->output[$chunk] .= str_replace("{\$".$key."}",$value,$template);
                    $appendOutput = false;
                } else $template = str_replace("{\$".$key."}",(string)$value,(string)$template);
            }
        }

        $template = $this->mapTransforms($template);
        return $template;
    }

    /**
     *
     * function: mapTransforms
     * Perform transformations on templating
     * @access public
     * @param string $template
     * @return string
     */
    private function mapTransforms($template) {
        preg_match_all("/\{[$]CURRENCY\[([^}]*)\]\}/si", $template, $currency);
        if (!empty($currency[1])) {
            foreach($currency[1] as $t) {
                if (is_numeric($t)) $template = str_replace('{$CURRENCY['.$t.']}','$'.number_format($t,2,'.',','),$template);
            }
        }

        preg_match_all("/\{[$]UPPER\[([^}]*)\]\}/si", $template, $upper);
        if (!empty($upper[1])) {
            foreach($upper[1] as $t) {
                $template = str_replace('{$UPPER['.$t.']}',strtoupper($t),$template);
            }
        }

        preg_match_all("/\{[$]LOWER\[([^}]*)\]\}/si", $template, $lower);
        if (!empty($lower[1])) {
            foreach($lower[1] as $t) {
                $template = str_replace('{$LOWER['.$t.']}',strtolower($t),$template);
            }
        }
        
        preg_match_all("/\{[$]TRIM\[([^}]*)\]\}/si", $template, $trim);
        if (!empty($trim[1])) {
            foreach($trim[1] as $t) {
                $template = str_replace('{$TRIM['.$t.']}',trim($t),$template);
            }
        }
        
        preg_match_all("/\{[$]DATE\[([^}]*)\]\}/si", $template, $date);
        if (!empty($date[1])) {
            foreach($date[1] as $t) {
                $tp = explode(',',$t);
                if (!is_numeric($tp[1])) $tp[1] = strtotime($tp[1]);
                
                $template = str_replace('{$DATE['.$t.']}',(string)date(str_replace('\'','',$tp[0]),$tp[1]),$template);
            }
        }

        return $template;
    }

    /**
     *
     * function: mapTemplate
     * Maps a template to available data by tmp tag
     * @access public
     * @param string $chunk
     * @return string
     */
    protected function mapTemplate($chunk=null) {
        $this->initChunk('GLOBAL');
        $this->template = $this->extractChunk($chunk);

        preg_match_all("/\<tmp:(.*?)\>/s",$this->template,$matches);
        if (!isset($matches[1])) return;

        foreach (array_reverse($matches[1]) as $chunk) {
            $chunk = preg_replace("/(\s)+(.*)?/", "", $chunk);

            $this->template = str_replace('{$tmp:'.$chunk.'}', '{__$tmp:'.$chunk.'}', $this->template);
            $this->initChunk($chunk);

            $ct = count($this->data[$chunk]);
            $this->output[$chunk] = '';

            $appendOutput = true;

            if (key_exists($chunk, $this->inherit) && !in_array($chunk, $this->hide)) continue;
            for ($i = 0; $i < $ct; $i++) {
                $template = $this->parseChunk($chunk,$ct,$i);
                $data = array_merge($this->getDefault($chunk),$this->data['GLOBAL'][0],$this->data[$chunk][$i]);
                $key = array_search($chunk,$this->inherit);

                if ($key) {
                    $this->data[$key][$i] = $this->data[$chunk][$i];
                    $sub = $this->parseChunk($key,$ct,$i);

                    preg_match_all("/\{[$]([^}]*)\}/si", $sub, $vars);
                    $keys = (isset($vars[1]) && !empty($vars[1]) && empty($this->data['GLOBAL'][0])) ? array_flip($vars[1]) : $data;

                    $sub = $this->parseData($key, $sub, $keys, $data, $ct, $i);
                    $template = preg_replace("/\<tmp:".$key."(.*?)\>(.*?)\<\/tmp:".$key."\>/s", trim($sub), $template);
                }

                preg_match_all("/\{[$]([^}]*)\}/si", $template, $vars);
                $keys = (isset($vars[1]) && !empty($vars[1]) && empty($this->data['GLOBAL'][0])) ? array_flip($vars[1]) : $data;

                $template = $this->parseData($chunk,$template,$keys,$data,$ct,$i);

                if ($appendOutput !== false) $this->output[$chunk] .= $template;
            }

            $this->output[$chunk] = preg_replace("/\{[$][^}]*\}/si", "", $this->output[$chunk]);
            if (in_array($chunk, $this->hide)) $this->output[$chunk] = "";

            $this->output[$chunk] = str_replace('$','\$',$this->output[$chunk]);
            $this->template = preg_replace('/\<tmp:'.$chunk.'(.*?)\>(.*?)\<\/tmp:'.$chunk.'\>/s', trim($this->output[$chunk]), $this->template);
        }
        
        foreach ($this->output as $chunk => $val) $this->template = str_replace('{__$tmp:'.$chunk.'}', $val, $this->template);
        
        unset($this->data[$chunk]);
        unset($this->output[$chunk]);

        return $this->template;
    }
}

