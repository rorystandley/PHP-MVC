<?php

class HomeController extends Controller {

	function __construct(){
		parent::__construct();
	}

	function beforeAction () {

    }  
    
    function index() {
        $this->set('title', 'PHP MVC');   
        $this->model('home');
        $this->view->load('home/index');  
    }

    function afterAction() {
    	
    }
	
}
