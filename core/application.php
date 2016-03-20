<?php
$url = @$_GET['url'];
$controller = '';

// Check to see if we are being pinged by the CLI
if ( isset($argv) ) {
    $url = $argv[1];
}

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
        $urlArray = array_filter($urlArray);
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
    // Check to see if the controller exists
    if ( !file_exists( '../application/controllers/' . $controllerName . '.php' ) ) {
        if ( isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json' ) {
            // Serve up some JSON
            $dispatch = new Api;
            $dispatch->response('Endpoint not found', 404);
        } else {
            // Serve up some HTML
            $dispatch = new ErrorController;
            call_user_func_array(array($dispatch, 'notFound'), []);
            exit;
        }
    }
    
    $dispatch = new $controllerName($controller, $action);
    if ( (int)method_exists($controllerName, $action) ) {
        call_user_func_array(array($dispatch, "beforeAction"), $queryString);
        call_user_func_array(array($dispatch, $action), $queryString);
        call_user_func_array(array($dispatch, "afterAction"), $queryString);
    } else {
        // We should check the Accept header type to server up the correct 404
        if ( isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json' ) {
            // Serve up some JSON
            $dispatch = new Api;
            $dispatch->response('Endpoint not found', 404);
        } else {
            // Serve up some HTML
            $dispatch = new ErrorController;
            call_user_func_array(array($dispatch, 'notFound'), []);
            exit;
        }
    }
}

callHook();