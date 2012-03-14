<?php

/** -----------------------------------------------
    pre-load files that will always be required...
    --------------------------------------------------------- */
require_once('core/Cache.php');
require_once('core/Constants.php');
require_once('core/DB.php');
require_once('core/Language.php');
require_once('core/Main.php');
require_once('core/Model.php');
require_once('core/Pager.php');
require_once('core/Session.php');
require_once('core/Templater.php');

/**
 *
 * function: dump
 * Used for debugging, dumps data
 * @access public
 * @param string $data
 * @param boolean $die (optional)
 * @return null
 */
function dump($data,$die=true) {
    echo '<pre>';
    if (is_array($data) || is_object($data)) { print_r($data); }
    else print($data);
    echo '</pre>';

    if ($die) exit();
}

/**
 *
 * function: escape
 * Escapes a string for safe use
 * @access public
 * @param string $str
 * @return string
 */
function escape($str) {
    $str = addslashes(str_replace("'","&apos;",$str));
    return $str;
}

/**
 *
 * function: systemCaching
 * Determine is system-wide setting for disallowing caching has been set
 * @access public
 * @return boolean
 */
function systemCaching() {
    if (defined('CACHE_ALLOW') && constant('CACHE_ALLOW') == 0) return false;
    else return true;
}

/**
 *
 * function: loadRoutes
 * Load routing files, in advance of checking DB routing
 * @access public
 * @param object $iniFile
 * @return Array
 */
function loadRoutes($iniFile) {
    $routesList = array();

    if (file_exists($iniFile)) {
        $cache = new Cache();
        $routesList = $cache->get(VAR_PREPEND.'store[environment[routes]['.md5($iniFile).']]');

        if (!empty($routesList)) $routesList = unserialize($routesList);
        else $routesList = array();

        $routes = parse_ini_file($iniFile,true);
        foreach($routes as $route) {
            $uriAssoc = $route['uri'].(!empty($route['base']) ? '@'.$route['base'] : '');
            $routesList[$uriAssoc] = array('base' => (!empty($route['base']) ? $route['base'] : false),
                                           'uri' => $route['uri'],
                                           'title' => $route['title'],
                                           'keywords' => (!empty($route['keywords']) ? $route['keywords'] : ''),
                                           'description' => (!empty($route['description']) ? $route['description'] : ''),
                                           'summary' => (!empty($route['summary']) ? $route['summary'] : ''),
                                           'class' => $route['class'],
                                           'directive' => (!empty($route['directive']) ? $route['directive'] : false),
                                           'pager' => (!empty($route['secure']) ? 'PageSSL' : 'Page'),
                                           'params' => (!empty($route['keywords']) ? $route['keywords'] : ''),
                                           'bypass' => (!empty($route['bypass']) ? true : false),
                                           'cache' => (!empty($route['cache']) ? true : false),
                                           'matching' => (!empty($route['matching']) ? true : false));
        }

        $cache->set(VAR_PREPEND.'store[environment[routes]['.md5($iniFile).']]',serialize($routesList));
    }

    unset($cache);
    return $routesList;
}

/**
 *
 * function: isMobileBrowser
 * Determine if the current browser is mobile
 * @access public
 * @return boolean
 */
function isMobileBrowser() {
    $mobile_browser = 0;
    if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android)/i',strtolower($_SERVER['HTTP_USER_AGENT']))) $mobile_browser++;
    if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) $mobile_browser++;

    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
    $mobile_agents = array('w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
                           'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
                           'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
                           'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
                           'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
                           'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
                           'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
                           'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
                           'wapr','webc','winw','winw','xda ','xda-');

    if (in_array($mobile_ua,$mobile_agents)) $mobile_browser++;
    if (strpos(strtolower($_SERVER['ALL_HTTP']),'operamini') > 0) $mobile_browser++;
    if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),' ppc;') > 0) $mobile_browser++;

    if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows') > 0 &&
        strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'iemobile') == 0) $mobile_browser = 0;

    if ($mobile_browser > 0) return true;
    else return false;
}

/**
 *
 * function: __autoload
 * Autoloader
 * @access public
 * @param string $class
 * @return null
 */
function __autoload($class) {
    $root = str_replace('src','',realpath(dirname(__FILE__)));
    $src = $root.'src/';
    
    if (file_exists($src.'master/'.$class.'.php')) {
        require_once($src.'master/'.$class.'.php');
        return;
    } elseif (file_exists($src.'core/'.$class.'.php')) {
        require_once($src.'core/'.$class.'.php');
        return;
    } elseif (file_exists($src.'models/'.$class.'.php')) {
        require_once($src.'models/'.$class.'.php');
        return;
    } elseif (file_exists($src.'controllers/'.$class.'.php')) {
        require_once($src.'controllers/'.$class.'.php');
        return;
    } elseif (file_exists($root.'custom/'.$_SERVER['SERVER_NAME'])) {
        if (file_exists($root.'custom/'.$_SERVER['SERVER_NAME'].'/core/'.$class.'.php')) {
            require_once($root.'custom/'.$_SERVER['SERVER_NAME'].'/core/'.$class.'.php');
            return;
        } elseif (file_exists($root.'custom/'.$_SERVER['SERVER_NAME'].'/models/'.$class.'.php')) {
            require_once($root.'custom/'.$_SERVER['SERVER_NAME'].'/models/'.$class.'.php');
            return;
        } elseif (file_exists($root.'custom/'.$_SERVER['SERVER_NAME'].'/controllers/'.$class.'.php')) {
            require_once($root.'custom/'.$_SERVER['SERVER_NAME'].'/controllers/'.$class.'.php');
            return;
        }
    } elseif (file_exists($src.'utilities')) {
        if ($handle = opendir($src.'utilities')) {
            while (false !== ($entry = readdir($handle))) {
                if (!in_array($entry,array('.','..')) && file_exists($src.'utilities/'.$entry.'/'.$class.'.php')) {
                    require_once($src.'utilities/'.$entry.'/'.$class.'.php');
                    return;
                }
            }
        }
    }
}

/** -----------------------------------------------
    load constants into APC...
    --------------------------------------------------------- */
$c = new Constants(true);
$constants = array();

if (!$c->checkConstants()) {
    $ini = parse_ini_file('../settings.ini',true);
    $constants = $c->buildConstants($ini);
    
    if (!empty($_SERVER['SERVER_NAME']) && file_exists('../custom/'.$_SERVER['SERVER_NAME'].'/settings.ini')) {
        $ini = parse_ini_file('../custom/'.$_SERVER['SERVER_NAME'].'/settings.ini',true);
        $constants = $c->buildConstants($ini,$constants);
    }

    if (!empty($constants)) $c->setConstants($constants);
}

?>