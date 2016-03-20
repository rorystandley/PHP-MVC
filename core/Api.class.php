<?php

class Api extends Controller {

    protected $method;

    function __construct($controller = null, $action = null){
        parent::__construct($controller, $action);

        $this->controller   = $controller;
        $this->action       = $action;

        // The data is stolen at this point by this variable, so we are allowing access to it throughout the object
        $this->inputString  = $this->input->apiPost();

        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");

        $this->method = $_SERVER['REQUEST_METHOD'];

        // Log the call
        $this->model('apilog');
        $username = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
        $actionHeader = ( isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] != "" ) ? $_SERVER['CONTENT_TYPE'] : $_SERVER['HTTP_ACCEPT'];
        $_SESSION['log_id'] = $this->apilog->create($username, $this->inputString, $this->header(), $this->controller.'/'.$this->action, $actionHeader);

        // Check to see if the request being made is available
        if ( $this->method != 'GET' && $this->method != 'POST' && $this->method != 'PATCH' && $this->method != 'PUT' ) {
            $this->response('Method Not Allowed', 405);
        }

        // We only care for JSON header type
        if ( $actionHeader == 'application/json' ) {
            
        } else {
            // Serve up some HTML
            $dispatch = new ErrorController;
            call_user_func_array(array($dispatch, 'notFound'), []);
            exit;
        }

    }

    /**
     * Return the header type
     * @return string
     */
    public function header() {
        return $this->method;
    }

    public function beforeAction () {
        $this->model('auth');
        // Handle Auth for the API
        $username = '';
        $password = '';
        if ( isset($_SERVER['PHP_AUTH_USER']) ) {
            $username = $_SERVER['PHP_AUTH_USER'];
        }
        if ( isset($_SERVER['PHP_AUTH_PW']) ) {
            $password = $_SERVER['PHP_AUTH_PW'];
        }

        if ( !$this->auth->basicAuth($username, $password) ) {
            $this->response('Not Authorised', 403);
        }
    }

    /**
     * Response from the Integration framework
     * @param  string  $data   
     * @param  integer $status 
     * @return null          
     */
    public function response($data = '', $status = 200) {

        header("Content-Type: application/json");
        header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));
        $str = array("message" => $data, "status" => $status);

        // Update our log row
        if ( isset($_SESSION['log_id']) ) {
            $this->model('apilog');
            $this->apilog->update($_SESSION['log_id'], $status, json_encode($str));
        }

        echo json_encode($str);
        exit;
    }

    /**
     * Response from the SCV API - As we might just be proxying through our API
     * @param  string $data 
     * @return null
     */
    public function scvResponse($data = '') {

        $data = json_decode($data);

        $status = 520;
        if ( isset($data->CODE) ) {
            $status = $data->CODE;
        }

        header("Content-Type: application/json");
        header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));

        // Update our log row
        if ( isset($_SESSION['log_id']) ) {
            $this->model('apilog');
            $this->apilog->update($_SESSION['log_id'], $status, json_encode($data));
        }

        echo json_encode($data);
        exit;
    }

    public function getUsername() {
        $username = '';
        if ( isset($_SERVER['PHP_AUTH_USER']) ) {
            $username = $_SERVER['PHP_AUTH_USER'];
        }
        return $username;
    }

    private function requestStatus($code = 200) {
        $status = array(  
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            403 => 'Not Authorised',   
            404 => 'Not Found',   
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
            520 => 'Unknown Error',
            999 => '',
        ); 
        return ($status[$code])?$status[$code]:$status[999]; 
    }

    public function afterAction() {
    	
    }
	
}
