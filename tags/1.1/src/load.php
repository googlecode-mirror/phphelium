<?php

/** -----------------------------------------------
    determine proper routing behavior for page...
    ----------------------------------------------- */
$cache = Cache::init();
$pageURI = $_SERVER['PHP_SELF'];

$hd = parse_url($_SERVER['HTTP_HOST']);
$host = explode('.', $hd['path']);
$subdomains = array_slice($host,0,count($host)-2);
if (!empty($subdomains) && count($subdomains) > 0) {
    if ($subdomains[0] <> (defined('DEFAULT_SUBDOMAIN') == true ? DEFAULT_SUBDOMAIN : 'www')) $pageURI .= '@'.implode('.',$subdomains);
}

$pageData = array();
$pagesList = $cache->get(VAR_PREPEND.'store[environment[pages]]');
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
            } elseif (substr_count($pageURI,$pageCompare) > 0) $pageData = $route;
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
                } elseif (substr_count($pageURI,$pageCompare) > 0) $pageData = $route;
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

        if ($pages = $db->getAll($sql,$pageOpts)) {
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
                              'cache' => ($pages[0]->cache ? true : false),
                              'matching' => ($pages[0]->matching ? true : false));
        } else {
            $sql = 'SELECT base,uri,title,keywords,description,summary,controller,directive,secure,params,bypass,cache,matching
                    FROM pages
                    WHERE ? LIKE CONCAT(uri,"%")';
            if (!empty($pageOpts[1])) $sql .= ' AND base = ?';
            else $sql .= ' AND base IS NULL';
            $sql .= ' AND matching = 1;';

            if ($pages = $db->getAll($sql,array($pageOpts))) {
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
                                  'cache' => ($pages[0]->cache ? true : false),
                                  'matching' => ($pages[0]->matching ? true : false));
            } else {
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

$cache->set(VAR_PREPEND.'store[environment[pages]]',serialize($pagesList));

/** -----------------------------------------------
    Build our page...
    ----------------------------------------------- */
$pager = new $pageData['pager']();
if (!empty($pageData['class'])) echo $pager->build($pageData);
else {
    $pageData['class'] = 'NoRoute';
    echo $pager->build($pageData);
}
