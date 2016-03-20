<?php

class ErrorController extends Controller {

	function __construct($controller = null, $action = null){
        parent::__construct($controller, $action);
	}

	function beforeAction () {

    }

    function notFound() {
        $this->set('title', '404 Not Found');
        $this->view->load("error/404");
        exit;
    }

    function afterAction() {
    	
    }
	
}
