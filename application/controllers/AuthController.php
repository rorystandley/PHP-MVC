<?php

class AuthController extends Controller {

	function __construct($controller = null, $action = null){
        parent::__construct($controller, $action);
	}

	function beforeAction () {

    }

    function login() {
        if ( $this->input->post() ) {
            // Someone is trying to register
            if ( $this->auth->login($this->input->post()) ) {
                $this->auth->redirect('/');
            }
        }
        $this->set('title', 'Login');
        $this->view->load("auth/login");
    }

    function register() {
        // I want people to be logged in to be able to register someone
        if ( !$this->auth->check() ) {
            $this->auth->redirect();
        }

        if ( $this->input->post() ) {
            // Someone is trying to register
            if ( $this->auth->register($this->input->post()) ) {
                $this->auth->redirect('/');
            }
        }
        $this->set('title', 'Register');
        $this->view->load("auth/register");
    }

    function logout() {
        $this->auth->logout();
    }

    function afterAction() {
    	
    }
	
}
