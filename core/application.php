<?php
$url = @$_GET['url'];
$controller = '';
include("config.php");

/**
 * Auto load classes
 */
spl_autoload_register(function($className) {

    if ( file_exists( '../application/controllers/' . ucfirst($className) . '.php' ) ) {
        @include_once( '../application/controllers/' . ucfirst($className) . '.php' );
    } else if ( file_exists( '../application/models/' . ucfirst($className) . '.php' ) ) {
        @include_once( '../application/models/' . ucfirst($className) . '.php' );
    } else if ( file_exists( '../core/' . ucfirst($className) . '.class.php' ) ) {
        @include_once( '../core/' . ucfirst($className) . '.class.php' );
    }else {
        /* Error Generation Code Here */
        die("Could not find the class $className.");
    }
});

// Routing
$routing = array(
    '/admin\/(.*?)\/(.*?)\/(.*)/' => 'admin/\1_\2/\3'
);

$default['controller'] = 'home';
$default['action'] = 'index';

function routeURL($url) {
    global $routing;
    foreach ( $routing as $pattern => $result ) {
            if ( preg_match( $pattern, $url ) ) {
                return preg_replace( $pattern, $result, $url );
            }
    }
    return ($url);
}

function callHook() {
    global $url;
    global $default;
    global $controller; 
    $queryString = array();
    if ( !isset($url) ) {
        $controller = $default['controller'];
        $action = $default['action'];
    } else {
        $url = routeURL($url);
        $urlArray = array();
        $urlArray = explode("/",$url);
        $controller = $urlArray[0];
        array_shift($urlArray);
        if (isset($urlArray[0])) {
            $action = $urlArray[0];
            array_shift($urlArray);
        } else {
            $action = 'index'; // Default Action
        }
        $queryString = $urlArray;
    }

    $controllerName = ucfirst($controller).'Controller';
    $dispatch = new $controllerName($controller, $action);
    if ( (int)method_exists($controllerName, $action) ) {
        call_user_func_array(array($dispatch, "beforeAction"), $queryString);
        call_user_func_array(array($dispatch, $action), $queryString);
        call_user_func_array(array($dispatch, "afterAction"), $queryString);
    } else {
        /* Error Generation Code Here */
        $dispatch = new ErrorController;
        call_user_func_array(array($dispatch, 'notFound'), []);
    }
}

callHook();
