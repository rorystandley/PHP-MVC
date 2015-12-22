<?php
class Controller {
     
    protected $_controller;
    protected $_action;
    protected $_template;

    function __construct($controller = null, $action = null) {
        $this->_controller = ucfirst($controller);
        $this->_action = $action;
        $this->view = new View;
    }

    /**
     * Extends the view class set function
     * @param string $name  name of variable
     * @param string $value value of variable
     */
    protected function set($name = '', $value = '') {
        $this->view->set($name, $value);
    }

    protected function model($model = '') {
        if ( $model != '' ) {
            $this->$model = new $model;
        }
    }
}