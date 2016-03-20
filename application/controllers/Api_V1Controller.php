<?php

class Api_v1Controller extends Api {

	function __construct($controller = null, $action = null){
        parent::__construct($controller, $action);
    }

    public function storage($action = '') {
        // We need to do something depending on the $action
        $this->model('scv');
        switch($action) {
            case 'addcolumn':
                if ( $this->scv->addStorageColumn($this->inputString) ) {
                    $this->response('Accepted', 202);
                }
                $this->response('ERROR', 400);
                break;
            default:
                $this->response('ERROR', 400);
                break;
        }
    }

    public function customers($action = '') {
        $this->model('scv');
        $result = $this->scv->customer($this->header(), $this->inputString, "/customers/$action");
        $this->scvResponse($result);
    }

    public function customer($action = '') {
        $this->model('scv'); 
        $this->model('triggerable'); 
        $result = $this->scv->customer($this->header(), $this->inputString, "/customer/$action");
        $this->scvResponse($result);
    }

    public function scheduled($query) {
        switch ($query) {
            case 'report':
                $this->model('scheduled');
                $result = $this->scheduled->report();
                $this->scvResponse($result);
                break;
            
            default:
                $this->response('ERROR', 400);
                break;
        }
    }
}
