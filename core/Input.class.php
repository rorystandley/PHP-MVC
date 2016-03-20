<?php
class Input {
    
    /**
     * Return a given $_GET value if it exists, else, return false
     * If no $key is passed through I am expecting all $_GEt variables returned to me
     * @param  string $key 
     * @return string/array
     */
    function get($key = '') {
        if ( $key != '' ) {
            return isset($_GET[$key]) ? $_GET[$key] : false;
        }
        return $_GET;
    }

    /**
     * Return a given $_POST value if it exists, else, return false
     * If no $key is passed through I am expecting all $_GEt variables returned to me
     * @param  string $key 
     * @return string/array
     */
    function post($key = '') {
        if ( $key != '' ) {
            return isset($_POST[$key]) ? $_POST[$key] : false;
        }
        return $_POST;
    }

    /**
     * TODO - Would prefer to handle this in the post()
     * For now it will live here
     * @return string body of POST
     */
    function apiPost() {
        return file_get_contents('php://input');
    }
}