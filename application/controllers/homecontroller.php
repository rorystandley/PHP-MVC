<?php
 
class HomeController extends Controller {

    function beforeAction () {

    }  
    
    function index() {
        $this->set('title','PHP MVC - My Application');    
    }

    function afterAction() {

    }
 
}