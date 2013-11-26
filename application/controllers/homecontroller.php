<?php
 
class HomeController extends Controller {

    function beforeAction () {

    }  
    
    function index() {
        $this->set('title','PHP MVC - My Application');
        // $this->Category->orderBy('name','ASC');
        // $this->Category->showHasOne();
        // $this->Category->showHasMany();
        // $this->Category->where('parent_id','0');
        // $categories = $this->Category->search();
        // $this->set('categories',$categories);
    
    }

    function afterAction() {

    }
 
}