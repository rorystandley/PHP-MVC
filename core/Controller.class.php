<?php
class Controller {
     
    protected $_controller;
    protected $_action;
    protected $_template;

    function __construct($controller = null, $action = null) {
        $this->_controller = ucfirst($controller);
        $this->_action = $action;
        $this->view = new View;
        $this->input = new Input;
    }

    /**
     * Extends the view class set function
     * @param string $name  name of variable
     * @param string $value value of variable
     */
    protected function set($name = '', $value = '') {
        $this->view->set($name, $value);
    }

    /**
     * Instantiate a give model
     * @param  string $model name of model
     * @return null
     */
    protected function model($model = '') {
        if ( $model != '' ) {
            $this->$model = new $model;
        }
    }
    /**
     * Redirect a user to a given route
     * @param  string $route
     * @param  array  $queryParam parameters that can be appended to redirect URL
     * @return null             
     */
    protected function redirect($route = "/", $queryParam = []) {
        $query = "/";
        if ( count($queryParam) > 0 ) {
            $query = "?";
            $count = 1;
            foreach ($queryParam as $key => $value) {
                $query .= "$key=$value";
                if ( $count != count($queryParam) ) {
                    $query .= '&';
                }
                $count++;
            }
        }
        header("Location: $route$query");
    }
}