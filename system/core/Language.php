<?php namespace Helium;

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
     * Load a language
     * @access public
     * @param string $ltmp
     * @param string $lang (optional)
     * @return string
     */
    public function loadLanguage($bundles=array('DEFAULT'),$ltmps=array('GLOBAL'),$lang=false) {
        if (!is_array($ltmps)) $ltmps = array($ltmps);
        $root = str_replace('system/core','',realpath(dirname(__FILE__)));
        
        if (empty($lang)) $lang = Session::getLanguage();
        
        $appendDefault = false;
        $bundleList = array();
        foreach($bundles as $bundle) {
            if ($bundle !== 'DEFAULT') $bundleList[] = $bundle;
            else $appendDefault = true;
        }

        if (!empty($appendDefault)) $bundleList[] = 'DEFAULT';
        
        $langList = array();
        foreach($bundleList as $bundle) {
            $langList[$bundle] = array();
            
            foreach($ltmps as $ltmp) {
                $langList[$bundle][$ltmp] = array();
                
                if (substr_count($ltmp,'.tmp')) $ltmp = str_replace('.tmp','',$ltmp);

                $cache = Cache::init();
                $tmpList = $cache->get('store[environment[language]['.$bundle.'-'.$ltmp.'-'.$lang.']');
                if (empty($tmpList)) {
                    $langFile = Language::loadLanguageFile($lang);
                    if (!empty($langFile) && !empty($langFile[$bundle]) && !empty($langFile[$bundle][$ltmp])) {
                        $langList[$bundle][$ltmp] = $langFile[$bundle][$ltmp];
                        $cache->set('store[environment[language]['.$bundle.'-'.$ltmp.'-'.$lang.']',serialize($langFile[$bundle][$ltmp]));
                    }
                } else $langList[$bundle][$ltmp] = unserialize($tmpList);
            }
        }
        
        return $langList;
    }

    /**
     *
     * function: loadLanguageFile
     * Load a language file
     * @access public
     * @param string $lang
     * @return string
     */
    public function loadLanguageFile($lang=false) {
        $langList = array();
        
        if (empty($lang)) $lang = Session::getLanguage();

        $root = str_replace('system/core','',realpath(dirname(__FILE__)));
        if (!empty($_SERVER['SERVER_NAME']) && file_exists($root.'src/custom/'.$_SERVER['SERVER_NAME'].'/language/'.$lang.'.xml')) $langFile = $root.'src/custom/'.$_SERVER['SERVER_NAME'].'/language/'.$lang.'.xml';
        else $langFile = $root.'language/'.$lang.'.xml';
        
        $cache = Cache::init();
        $langList = $cache->get('store[environment[languageFile]['.md5($langFile).']');
        if (!empty($langList)) return unserialize($langList);
        
        if (file_exists($langFile)) $xml = (array)simplexml_load_file($langFile);
        if (!empty($xml)) {
            $langList = array();

            $bundleList = array();
            if (is_array($xml['bundle'])) {
                foreach($xml['bundle'] as $bundle) {
                    $bname = (string)$bundle['name'];
                    $bundleTmps = $bundle->tmps;

                    $bundleList[$bname] = $bundleTmps;
                }
            } else $bundleList[(string)$xml['bundle']['name']] = $xml['bundle']->tmps;
            
            foreach($bundleList as $bid => $bundle) {
                $langBlocks = array();
                foreach($bundle->tmp as $t) {
                    $opt = (string)$t['name'];
                    $langBlocks[$opt] = $t->phrases;
                }
                
                if (!empty($langBlocks)) {
                    foreach($langBlocks as $block => $langBlock) {
                        $langBlock = (array)$langBlock;
                        if (is_array($langBlock['phrase'])) {
                            foreach($langBlock['phrase'] as $phrase) {
                                $phrase = (array)$phrase;
                                if (!empty($phrase['id'])) $langList[$bid][$block][$phrase['id']] = trim((string)$phrase['sub']);
                            }
                        } else {
                            $langBlock = (array)$langBlock['phrase'];
                            $langList[$bid][$block][$langBlock['id']] = trim((string)$langBlock['sub']);
                        }
                    }
                }
                
                unset($langBlocks);
            }
        }
        
        $cache->set('store[environment[languageFile]['.md5($langFile).']',serialize($langList));
        return $langList;
    }

    public function getSub($id,$bundle='DEFAULT',$ltmp='GLOBAL',$lang=false) {
        $language = Language::loadLanguageFile($lang);
        return $language[$bundle][$ltmp][$id];
    }

    public function available() {
        $root = str_replace('system/core','',realpath(dirname(__FILE__)));

        $packs = array();
        if ($handle = opendir($root.'language')) {
            while (false !== ($entry = readdir($handle))) {
                if (!in_array($entry,array('.','..'))) {
                    $packs[] = $entry;
                }
            }
        }

        return $packs;
    }
}