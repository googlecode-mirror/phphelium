<?php

/*
 * Language.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Process language actions
 */

class Language {
    private $language;

    function __construct($lang=false) {
        if (empty($lang)) $lang = Session::getLanguage();
        $this->language = $lang;
    }

    /**
     *
     * function: getLanguage
     * Get the active language
     * @access public
     * @return string
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     *
     * function: loadLanguage
     * Load a language file
     * @access public
     * @param string $ltmp
     * @param string $lang (optional)
     * @return string
     */
    public function loadLanguage($ltmp='GLOBAL',$lang=false) {
        $root = str_replace('src/core','',realpath(dirname(__FILE__)));
        
        if (empty($lang)) $lang = Session::getLanguage();
        if (substr_count($ltmp,'.tmp')) $ltmp = str_replace('.tmp','',$ltmp);

        $cache = new Cache();
        if (defined('VAR_PREPEND')) $langList = $cache->get(VAR_PREPEND.'store[environment[language]['.$ltmp.'-'.$lang.']');
        if (empty($langList)) {
            $langFile = Language::loadLanguageFile($lang);
            if (!empty($langFile) && !empty($langFile[$ltmp])) {
                $langList = $langFile[$ltmp];
                if (defined('VAR_PREPEND')) $cache->set(VAR_PREPEND.'store[environment[language]['.$ltmp.'-'.$lang.']',serialize($langList));
            }
        } else $langList = unserialize($langList);
        
        if (!empty($langList)) {
            $tmp = Templater::init();
            foreach($langList as $pid => $phrase) {
                $tmp->setGlobal('LANG['.$pid.']',$phrase);
            }
        }
    }

    public function loadLanguageFile($lang=false) {
        $langList = array();
        
        if (empty($lang)) $lang = Session::getLanguage();

        $root = str_replace('src/core','',realpath(dirname(__FILE__)));
        if (!empty($_SERVER['SERVER_NAME']) && file_exists($root.'custom/'.$_SERVER['SERVER_NAME'].'/language/'.$lang.'.xml')) $langFile = $root.'/custom/'.$_SERVER['SERVER_NAME'].'/language/'.$lang.'.xml';
        else $langFile = $root.'language/'.$lang.'.xml';
        
        $cache = new Cache();
        if (defined('VAR_PREPEND')) {
            $langList = $cache->get(VAR_PREPEND.'store[environment[languageFile]['.md5($langFile).']');
            if (!empty($langList)) return unserialize($langList);
        }
        
        if (file_exists($langFile)) $xml = (array)simplexml_load_file($langFile);
        if (!empty($xml)) {
            $langBlocks = array();
            foreach($xml['tmp'] as $t) {
                $opt = (string)$t['name'];
                $langBlocks[$opt] = $t->phrases;
            }
            
            if (!empty($langBlocks)) {
                foreach($langBlocks as $block => $langBlock) {
                    $langBlock = (array)$langBlock;                    
                    if (is_array($langBlock['phrase'])) {
                        foreach($langBlock['phrase'] as $phrase) {
                            $phrase = (array)$phrase;
                            if (!empty($phrase['id'])) $langList[$block][$phrase['id']] = trim((string)$phrase['sub']);
                        }
                    } else {
                        $langBlock = (array)$langBlock['phrase'];
                        $langList[$block][$langBlock['id']] = trim((string)$langBlock['sub']);
                    }
                }
            }

            unset($langBlocks);
        }
        
        if (defined('VAR_PREPEND')) $cache->set(VAR_PREPEND.'store[environment[languageFile]['.md5($langFile).']',serialize($langList));
        return $langList;
    }

    public function getSub($id,$ltmp='GLOBAL',$lang=false) {
        $language = Language::loadLanguageFile($lang);
        return $language[$ltmp][$id];
    }
}

