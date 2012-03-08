<?php

/*
 * Pager.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all page specific information
 */

abstract class Pager {
    function __construct() {}

     /**
     *
     * function: build
     * Build a proper page
     * @access public
     * @param array $data
     * @return null
     */
    public function build($data = array('class'=>false,'title'=>DEFAULT_TITLE,'params'=>array(),'bypass'=>false,'cache'=>false)) {
        if (!empty($data['class']) && !empty($data['title'])) {
            if (!empty($data['cache'])) $cached = $this->getPageCache();
            
            $data['params']['pageData'] = array('content' => $data['class'],
                                                'title' => $data['title']);

            if (!empty($data['keywords'])) $data['params']['pageData']['keywords'] = $data['keywords'];
            if (!empty($data['description'])) $data['params']['pageData']['description'] = $data['description'];
            if (!empty($data['summary'])) $data['params']['pageData']['summary'] = $data['summary'];
            if (!empty($data['directive'])) $data['params']['pageData']['directive'] = $data['directive'];
            $data['params']['pageData']['cache'] = false;

            $class = new $data['class']($data['params']);

            if ($_POST) {
                if (!empty($cached)) return $cached;
                if (!empty($data['directive'])) $html = $class->$data['directive']();
                else $html = $class->action();
            } elseif (empty($_REQUEST['AJAX'])) {
                if (!empty($data['directive'])) $display = $data['directive'];
                else $display = 'display';

                if ($data['bypass']) {
                    if (!empty($cached)) return $cached;
                    $html = $class->$display();
                } else {
                    if (!empty($class->cache)) $data['params']['pageData']['cache'] = $data['cache'];
                    $data['cache'] = false;

                    $main = new Main($data['params']);
                    $html = $main->display();
                }
            } else {
                if (!empty($cached)) return $cached;
                if (!empty($data['directive'])) $html = $class->$data['directive']();
                else $html = $class->display();
            }

            if (!empty($html)) {
                if (!empty($data['cache'])) $cached = $this->setPageCache($html);
                return $html;
            }

            throw new Error('Page failed to build');
        } else throw new Error('Page failed to build');
    }

    /**
     *
     * function: getPageCache
     * Get a cache of page output
     * @access public
     * @return string
     */
    public function getPageCache() {
        $cache = new Cache();
        $pageCache = $cache->get(VAR_PREPEND.'store[environment[pageCache]['.md5($_SERVER['REQUEST_URI']).']');
        if (!empty($pageCache)) return $pageCache;
        else return false;
    }

    /**
     *
     * function: setPageCache
     * Get a cache of page output
     * @access public
     * @param string $html
     * @return null
     */
    public function setPageCache($html) {
        $cache = new Cache();
        $cache->set(VAR_PREPEND.'store[environment[pageCache]['.md5($_SERVER['REQUEST_URI']).']',$html);
    }
}

?>
