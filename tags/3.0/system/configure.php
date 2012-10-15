<?php namespace Helium;

set_include_path(".:".SRC."/default:/usr/share/php");
date_default_timezone_set(TIMEZONE);

Session::init();

/** -----------------------------------------------
    if we should be in mobile mode,
    but are not, go mobile...
    ----------------------------------------------- */
if (Session::isMobile() == true) {
    if (defined('MOBILE_SUBDOMAIN') && defined('DEFAULT_URI')) {
        $mobileDomain = MOBILE_SUBDOMAIN.'.'.DEFAULT_URI;
        if ($_SERVER['HTTP_HOST'] <> $mobileDomain) {
            $uri = 'http://'.$mobileDomain.$_SERVER['PHP_SELF'];
            header('Location: '.$uri);
            exit();
        }
    }
}

/** -----------------------------------------------
    load language data...
    ----------------------------------------------- */
$lang = new Language();
if ($language = $lang->getLanguage()) {
    Session::setLanguage($language);
} else Session::setLanguage('en');

/** -----------------------------------------------
    set a few important helpers...
    ----------------------------------------------- */
ini_set('display_errors', DISPLAY_ERRORS);

function exception_error_handler($errno,$errstr,$errfile,$errline) {
    throw new ErrorManager($errstr,0,$errno,$errfile,$errline);
}

set_error_handler('Helium\exception_error_handler', E_ALL);