<?php
class Controller {
     
    protected   $_controller,
                $_action;

    function __construct($controller = null, $action = null) {
        $this->_controller = ucfirst($controller);
        $this->_action = $action;
        $this->view = new View;
        $this->input = new Input;
        $this->auth = new Auth;
        $this->set('controller', $controller);
        $this->set('auth', $this->auth);
        date_default_timezone_set("Europe/London");
        $this->date = new DateTime();
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
     * Instantiate a given model
     * Arguments for now will only work with one inst' of a model
     * @param  string $models name of model
     * @return null
     */
    protected function model($models = '', $arguments = []) {
        if ( count($models) > 1 ) {
            // We have an array of models to work with
            foreach ($models as $model) {
                $this->$model = new $model;
            }
        } else if ( $models != '' ) {
            if ( count($arguments) > 0 ) {
                $this->$models = new $models( implode(',', $arguments) );
            } else {
                $this->$models = new $models;
            }
        }
    }
    
    /**
     * Redirect a user to a given route
     * @param  string $route
     * @param  array  $queryParam parameters that can be appended to redirect URL
     * @return null             
     */
    protected function redirect($route = "/", $queryParam = []) {
        $query = "";
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