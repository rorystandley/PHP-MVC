<?php

class ErrorController extends Controller {

	function __construct(){
		parent::__construct();
	}

	function beforeAction () {

    } 

    function notFound() {
        $this->set('title', '404 Not Found'); 
        $this->view->load("error/404");
    }

    function afterAction() {
    	
    }
	
}
