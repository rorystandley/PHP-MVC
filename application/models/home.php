<?php
class Home extends model {

	protected $tableName        = 'name',
              $primaryKey       = 'id',
              $columnNames     = [
                
              ];
     
    function __construct($id = null, $data = null) {
		parent::__construct($id, $data);
    }
}