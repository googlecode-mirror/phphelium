<?php
namespace Helium;

/** -----------------------------------------------
    determine proper routing behavior for page...
    ----------------------------------------------- */
$cache = Cache::init();
$pageURI = $_SERVER['PHP_SELF'];

$hd = parse_url($_SERVER['HTTP_HOST']);
if (!empty($hd) && !empty($hd['path'])) {
    $host = explode('.', $hd['path']);
    $subdomains = array_slice($host,0,count($host)-2);
    if (!empty($subdomains) && count($subdomains) > 0) {
        if ($subdomains[0] <> (defined('DEFAULT_SUBDOMAIN') == true ? DEFAULT_SUBDOMAIN : 'www')) $pageURI .= '@'.implode('.',$subdomains);
    }
}

$pageData = array();
$pagesList = $cache->get('store[environment[pages]]');
if (!empty($pagesList)) {
    $pagesList = unserialize($pagesList);
    if (!empty($pagesList[$pageURI])) $pageData =  $pagesList[$pageURI];
    else {
        foreach($pagesList as $pageRoute) {
            $pageCompare = $pageRoute['uri'].(!empty($pageRoute['base']) ? '@'.$pageRoute['base'] : '');
            if (!empty($pageRoute['matching']) && substr_count($pageURI,$pageCompare)) {
                $pageData = $pageRoute;
                break;
            }
        }
    }
} else $pagesList = array();

if (empty($pageData)) {
    $pagesList = array();

    /** -----------------------------------------------
        load global routing registry
        ----------------------------------------------- */
    if (file_exists(ROOT.'routes.ini')) {
        $routes = loadRoutes(ROOT.'routes.ini');
        foreach($routes as $rid => $route) {
            $pageCompare = $route['uri'].(!empty($route['base']) ? '@'.$route['base'] : '');
            $pagesList[$pageCompare] = $route;
            if (empty($route['matching'])) {
                if ($pageCompare == $pageURI) $pageData = $route;
            } else {
                $uriParts = explode('@',$pageURI);
                $compareParts = explode('@',$pageCompare);
                if (substr_count($uriParts[0],$compareParts[0]) > 0) {
                    if (empty($uriParts[1]) && empty($compareParts[1])) $pageData = $route;
                    elseif ($uriParts[1] == $compareParts[1]) $pageData = $route;
                }
            }
        }
    }

    /** -----------------------------------------------
        load site-specific routing registry
        ----------------------------------------------- */
    if (!empty($_SERVER['SERVER_NAME'])) {
        if (file_exists(ROOT.'custom/'.$_SERVER['SERVER_NAME'].'/routes.ini')) {
            $pagesList = array();
            $pageData = array();

            $routes = loadRoutes(ROOT.'custom/'.$_SERVER['SERVER_NAME'].'/routes.ini');
            foreach($routes as $rid => $route) {
                $pageCompare = $route['uri'].(!empty($route['base']) ? '@'.$route['base'] : '');
                $pagesList[$pageCompare] = $route;
                if (empty($route['matching'])) {
                    if ($pageCompare == $pageURI) $pageData = $route;
                } else {
                    $uriParts = explode('@',$pageURI);
                    $compareParts = explode('@',$pageCompare);
                    if (substr_count($uriParts[0],$compareParts[0]) > 0) {
                        if (empty($uriParts[1]) && empty($compareParts[1])) $pageData = $route;
                        elseif ($uriParts[1] == $compareParts[1]) $pageData = $route;
                    }
                }
            }
        }
    }

    if (empty($pageData)) {
        $db = new DB();

        $pageOpts = explode('@',$pageURI);
        $pageData = array('base' => false,
                          'uri' => false,
                          'title' => DEFAULT_TITLE,
                          'keywords' => DEFAULT_KEYWORDS,
                          'description' => DEFAULT_DESCRIPTION,
                          'summary' => DEFAULT_SUMMARY,
                          'class' => 'NoRoute',
                          'directive' => false,
                          'pager' => 'Page',
                          'params' => false,
                          'bypass' => false,
                          'cache' => false,
                          'matching' => false);

        $sql = 'SELECT base,uri,title,keywords,description,summary,controller,directive,secure,params,bypass,cache,matching
                FROM pages
                WHERE uri = ?';
        if (!empty($pageOpts[1])) $sql .= ' AND base = ?';
        else $sql .= ' AND base IS NULL';
        $sql .= ' AND matching = 0;';

        $pages = $db->getAll($sql,$pageOpts);
        if (!empty($pages)) {
            $pageData = array('base' => (!empty($pages[0]->base) ? $pages[0]->base : false),
                              'uri' => $pages[0]->uri,
                              'title' => (!empty($pages[0]->title) ? DEFAULT_TITLE.' - '.$pages[0]->title : DEFAULT_TITLE),
                              'keywords' => $pages[0]->keywords,
                              'description' => $pages[0]->description,
                              'summary' => $pages[0]->summary,
                              'class' => $pages[0]->controller,
                              'directive' => (!empty($pages[0]->directive) ? $pages[0]->directive : false),
                              'pager' => ($pages[0]->secure ? 'PageSSL' : 'Page'),
                              'params' => unserialize($pages[0]->params),
                              'bypass' => ($pages[0]->bypass ? true : false),
                              'cache' => ($pages[0]->cache ? $pages[0]->cache : false),
                              'matching' => ($pages[0]->matching ? true : false));
        } else {
            $sql = 'SELECT base,uri,title,keywords,description,summary,controller,directive,secure,params,bypass,cache,matching
                    FROM pages
                    WHERE ? LIKE CONCAT(uri,"%")';
            if (!empty($pageOpts[1])) $sql .= ' AND base = ?';
            else $sql .= ' AND base IS NULL';
            $sql .= ' AND matching = 1;';

            $pages = $db->getAll($sql,array($pageOpts));
            if (!empty($pages)) {
                $pageData = array('base' => (!empty($pages[0]->base) ? $pages[0]->base : false),
                                  'uri' => $pages[0]->uri,
                                  'title' => (!empty($pages[0]->title) ? DEFAULT_TITLE.' - '.$pages[0]->title : DEFAULT_TITLE),
                                  'keywords' => $pages[0]->keywords,
                                  'description' => $pages[0]->description,
                                  'summary' => $pages[0]->summary,
                                  'class' => $pages[0]->controller,
                                  'directive' => (!empty($pages[0]->directive) ? $pages[0]->directive : false),
                                  'pager' => ($pages[0]->secure ? 'PageSSL' : 'Page'),
                                  'params' => unserialize($pages[0]->params),
                                  'bypass' => ($pages[0]->bypass ? true : false),
                                  'cache' => ($pages[0]->cache ? $pages[0]->cache : false),
                                  'matching' => ($pages[0]->matching ? true : false));
            } else {
                // we're going to need the Request class,
                // so let's load it...
                require_once('core/Request.php');

                $req = new Request();
                if ($req->uri[1] == 'c') {
                    $pageData['bypass'] = 1;
                    if (class_exists($req->uri[2])) $pageData['class'] = $req->uri[2];
                    else $pageData['class'] = 'NoRoute';
                } elseif (Request::getURI(1)) $pageData['class'] = 'NoRoute';
                else $pageData['class'] = 'Home';
            }
        }

        $pagesList[$pageURI] = $pageData;
    }
}

$cache->set('store[environment[pages]]',serialize($pagesList));

/** -----------------------------------------------
    Build our page...
    ----------------------------------------------- */
if ((int)$pageData['cache'] == 2) {
    $pageCache = Pager::getPageCache();
    if (!empty($pageCache)) $content = $pageCache;
}

if (empty($content)) {
    $root = realpath(dirname(__FILE__));
    
    // okay, without a cache we're going to need this stuff...
    require_once($root.'/core/Model.php');
    require_once($root.'/core/Pager.php');
    require_once($root.'/core/Templater.php');
    
    $pageData['pager'] = 'Helium\\'.$pageData['pager'];
    
    $pager = new $pageData['pager']();
    if (!empty($pageData['class'])) $content = $pager->build($pageData);
    else {
        $pageData['class'] = 'NoRoute';
        $content = $pager->build($pageData);
    }

    if ((int)$pageData['cache'] == 2) Pager::setPageCache($content);
}

if (!empty($content)) echo $content;
else throw new Error('Page failed to build');