<?php

class HomeController extends Controller {

	function __construct($controller = null, $action = null){
        parent::__construct($controller, $action);
	}

	function beforeAction () {
        if ( !$this->auth->check() ) {
            $this->auth->redirect();
        }
    }  
    
    function index() {
        $this->set('title', 'Overview - '.getenv("SCV_TITLE")); 
        $this->view->load('home/index');
        if ( $this->input->get('success') ) {
            $this->set('notification', '<div class="alert alert-success" role="alert">'.$this->input->get('success').'</div>');
        }
        if ( $this->input->get('warning') ) {
            $this->set('notification', '<div class="alert alert-danger" role="alert">'.$this->input->get('warning').'</div>');
        }
    }

    function afterAction() {
    	
    }
	
}
