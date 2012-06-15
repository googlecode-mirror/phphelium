<?php
namespace Helium;

/** -----------------------------------------------
    pre-load files that will always be required...
    --------------------------------------------------------- */

$systemDir = realpath(dirname(__FILE__)).'/core/';

require_once($systemDir.'Cache.php');
require_once($systemDir.'Constants.php');
require_once($systemDir.'DB.php');
require_once($systemDir.'Session.php');
require_once($systemDir.'Language.php');

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
        $cache = Cache::init();
        $routesList = $cache->get('store[environment[routes]['.md5($iniFile).']]');

        if (!empty($routesList)) $routesList = unserialize($routesList);
        else $routesList = array();
        
        $routes = parse_ini_file($iniFile,true);
        foreach($routes as $route) {
            $uriAssoc = $route['uri'].(!empty($route['base']) ? '@'.$route['base'] : '');
            $routesList[$uriAssoc] = array('base' => (!empty($route['base']) ? $route['base'] : false),
                                           'uri' => $route['uri'],
                                           'title' => (!empty($route['title']) ? DEFAULT_TITLE.' - '.$route['title'] : DEFAULT_TITLE),
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

        $cache->set('store[environment[routes]['.md5($iniFile).']]',serialize($routesList));
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
    if (!empty($_SERVER['ALL_HTTP']) && strpos(strtolower($_SERVER['ALL_HTTP']),'operamini') > 0) $mobile_browser++;
    if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),' ppc;') > 0) $mobile_browser++;

    if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows') > 0 &&
        strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'iemobile') == 0) $mobile_browser = 0;

    if ($mobile_browser > 0) return true;
    else return false;
}

/**
 *
 * function: autoloader
 * Autoloader
 * @access public
 * @param string $class
 * @return null
 */
function autoloader($class) {
    $class = str_replace('Helium\\','',$class);
    $cache = Cache::init();
    //$croute = $cache->get('store[environment[autoload]]['.$class.']');
    if (empty($croute)) {
        $croute = false;

        $root = str_replace('system','',realpath(dirname(__FILE__)));
        $src = $root.'src/';
        $base = $src.'base/';
        $cust = $src.'custom/';
        $sys = $root.'system/';
        
        if (file_exists($cust.$_SERVER['SERVER_NAME'])) {
            if (file_exists($cust.$_SERVER['SERVER_NAME'].'/core/'.$class.'.php')) $croute = $cust.$_SERVER['SERVER_NAME'].'/core/'.$class.'.php';
            elseif (file_exists($cust.$_SERVER['SERVER_NAME'].'/models/'.$class.'.php')) $croute = $cust.$_SERVER['SERVER_NAME'].'/models/'.$class.'.php';
            elseif (file_exists($cust.$_SERVER['SERVER_NAME'].'/controllers/'.$class.'.php')) $croute = $cust.$_SERVER['SERVER_NAME'].'/controllers/'.$class.'.php';
        }

        if (empty($croute)) {
            if (file_exists($sys.'master/'.$class.'.php')) $croute = $sys.'master/'.$class.'.php';
            elseif (file_exists($sys.'core/'.$class.'.php')) $croute = $sys.'core/'.$class.'.php';
            elseif (file_exists($base.'models/'.$class.'.php')) $croute = $base.'models/'.$class.'.php';
            elseif (file_exists($base.'controllers/'.$class.'.php')) $croute = $base.'controllers/'.$class.'.php';
            elseif (file_exists($root.'utilities')) {
                $handle = opendir($root.'utilities');
                if (!empty($handle)) {
                    while (false !== ($entry = readdir($handle))) {
                        if (!in_array($entry,array('.','..')) && file_exists($root.'utilities/'.$entry.'/'.$class.'.php')) {
                            $croute = $root.'utilities/'.$entry.'/'.$class.'.php';
                            break;
                        }
                    }
                }
            }
        }
    }
    
    if (!empty($croute)) {
        $cache->set('store[environment[autoload]]['.$class.']',$croute);
        require_once($croute);
        return;
    } else throw new Error('That file does not exist',0,E_USER_ERROR);
}

spl_autoload_register('Helium\autoloader');

/** -----------------------------------------------
    load constants into APC...
    --------------------------------------------------------- */
$c = new Constants(true);
$constants = array();

if (file_exists('../settings.ini') == true) {
    $ini = parse_ini_file('../settings.ini',true);
    if ($ini['system']['cacheAllow'] == 0) $c->clearConstants();
} else $ini = array();

if (!$c->checkConstants()) {
    $constants = $c->buildConstants($ini);
    
    if (!empty($_SERVER['SERVER_NAME']) && file_exists('../src/custom/'.$_SERVER['SERVER_NAME'].'/settings.ini')) {
        $ini = parse_ini_file('../src/custom/'.$_SERVER['SERVER_NAME'].'/settings.ini',true);
        $constants = $c->buildConstants($ini,$constants);
    }

    if (!empty($constants)) $c->setConstants($constants);
}

// turn on or off the debug mode...
if (!empty($constants['DEBUG_MODE'])) $_SESSION['system']['DEBUG'] = true;
else $_SESSION['system']['DEBUG'] = false;