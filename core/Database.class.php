<?php
class Database{
	/* 
	 * Create variables for credentials to MySQL database
	 */
    public $glob;
	
	/*
	 * Extra variables that are required by other function such as boolean con variable
	 */
	private $con = false; // Check to see if the connection is active
    public $mysql_link;
	private $result = array(); // Any results from a query will be stored here
    private $myQuery = "";// used for debugging process with SQL return
    private $numResults = "";// used for returning the number of rows
    public $affectedRows = null;
    public $error = null;

    // Construct function
    public function __construct(){
        global $dbCreds;
        $this->glob =& $dbCreds;
    }
	
	// Function to make connection to database
	public function connect(){
		if(!$this->con){
			$myconn = @mysql_connect($this->glob['db_host'],$this->glob['db_user'],$this->glob['db_pass']);  // mysql_connect() with variables defined at the start of Database class
            if($myconn){
                mysql_set_charset('utf8', $myconn);
                $this->mysql_link = $myconn;
            	$seldb = @mysql_select_db($this->glob['db_name'],$myconn); // Credentials have been pass through mysql_connect() now select the database
                if($seldb){
                	$this->con = true;
                    return true;  // Connection has been made return TRUE
                }else{
                	array_push($this->result,mysql_error($this->mysql_link)); 
                    return false;  // Problem selecting database return FALSE
                }  
            }else{
            	array_push($this->result, 'Something unexpected has gone wrong');
                return false; // Problem connecting return FALSE
            }  
        }else{  
            return true; // Connection has already been made return TRUE 
        }  	
	}
	
	// Function to disconnect from the database
    public function disconnect(){
    	// If there is a connection to the database
    	if($this->con){
    		// We have found a connection, try to close it
    		if(@mysql_close($this->mysql_link)){
    			// We have successfully closed the connection, set the connection variable to false
    			$this->con = false;
				// Return true tjat we have closed the connection
				return true;
			}else{
				// We could not close the connection, return false
				return false;
			}
		}
    }

    private function checkConnection() {
        if ($this->mysql_link == null) {
            $this->connect();
        }
    }
	
	public function sql($sql,$type=NULL){
        $this->checkConnection();
		$query = @mysql_query($sql, $this->mysql_link);
        $this->myQuery = $sql; // Pass back the SQL
        $this->result = array();
        $this->error = null;
		if($query){
			// If the query returns >= 1 assign the number of rows to numResults
            if($type == "select"){
                $this->numResults = mysql_num_rows($query);
                for($i = 0; $i < $this->numResults; $i++){
                    $r = mysql_fetch_array($query);
                    $key = array_keys($r);
                    for($x = 0; $x < count($key); $x++){
                        // Sanitizes keys so only alphavalues are allowed
                        if(!is_int($key[$x])){
                            if(mysql_num_rows($query) >= 1){
                                $this->result[$i][$key[$x]] = $r[$key[$x]];
                            }else if(mysql_num_rows($query) < 1){
                                $this->result[$i][$key[$x]] = null;
                            }
                        }
                    }
                }
            }else if($type == "insert"){
            	array_push($this->result,mysql_insert_id($this->mysql_link));
            }else if($type == "update"){
                array_push($this->result,mysql_affected_rows($this->mysql_link));
                return true; // Update has been successful
            }else{
                $this->affectedRows = mysql_affected_rows($this->mysql_link);
                array_push($this->result,"true");
                return true; // Query was successful
            }
		}else{
			array_push($this->result, mysql_error($this->mysql_link));
            $this->error = mysql_error($this->mysql_link);
            //file_put_contents(dirname(__FILE__).'/../logs/error_log.log', $_SERVER['REMOTE_ADDR'].'--'.$this->error.'--'.$sql, FILE_APPEND);
			return false; // No rows were returned
		}
	}
	
	// Function to SELECT from the database
    public function select($table, $rows = '*', $join = null, $where = null, $order = null, $useObject = 'ARRAY'){
        $this->checkConnection();
    	// Create query from the variables passed to the function
    	$q = 'SELECT '.$rows.' FROM '.$table;
		if($join != null){
			$q .= ' '.$join;
		}
		if($where != null){
        	$q .= ' WHERE '.$where;
		}
        if($order != null){
            $q .= ' ORDER BY '.$order;
		}
        $this->myQuery = $q; // Pass back the SQL
		// Check to see if the table exists
        if($this->tableExists($table)){
        	// The table exists, run the query
        	$query = @mysql_query($q, $this->mysql_link);
			if($query){
				// If the query returns >= 1 assign the number of rows to numResults
				$this->numResults = mysql_num_rows($query);
				// Loop through the query results by the number of rows returned
                switch($useObject) {
                    case 'OBJECT':
                        // TODO - Need to test this thoroughly as i have literally just thrown this in for the moment
                        $this->result = mysql_fetch_object($query);
                    break;
                    case 'ARRAY':
                    default:
                        for($i = 0; $i < $this->numResults; $i++){
                            $r = mysql_fetch_array($query);
                            $key = array_keys($r);
                            for($x = 0; $x < count($key); $x++){
                                // Sanitizes keys so only alphavalues are allowed
                                if(!is_int($key[$x])){
                                    if(mysql_num_rows($query) > 1){
                                        $this->result[$i][$key[$x]] = $r[$key[$x]];
                                    }else if(mysql_num_rows($query) < 1){
                                        $this->result = null;
                                    }else{
                                        $this->result[$key[$x]] = $r[$key[$x]];
                                    }
                                }
                            }
                        }
                    break;
                }
				
				return $this->numResults; // Query was successful
			}else{
				array_push($this->result,mysql_error($this->mysql_link));
				return false; // No rows where returned
			}
      	}else{
      		return false; // Table does not exist
    	}
    }
	
	// Function to insert into the database
    public function insert($table,$params=array()){
        $this->checkConnection();
    	// Check to see if the table exists
    	 if($this->tableExists($table)){
    	 	$sql='INSERT INTO '.$table.' ('.implode(',',array_keys($params)).') VALUES (\'' . implode('\', \'', $params) . '\')';
            $this->myQuery = $sql; // Pass back the SQL
            // Make the query to insert to the database
            if($ins = @mysql_query($sql, $this->mysql_link)){
            	array_push($this->result,mysql_insert_id($this->mysql_link));
                return true; // The data has been inserted
            }else{
            	array_push($this->result,mysql_error($this->mysql_link));
                return false; // The data has not been inserted
            }
        }else{
        	return false; // Table does not exist
        }
    }
	
	//Function to delete table or row(s) from database
    public function delete($table,$where = null){
        $this->checkConnection();
    	// Check to see if table exists
    	 if($this->tableExists($table)){
    	 	// The table exists check to see if we are deleting rows or table
    	 	if($where == null){
                $delete = 'DELETE '.$table; // Create query to delete table
            }else{
                $delete = 'DELETE FROM '.$table.' WHERE '.$where; // Create query to delete rows
            }
            // Submit query to database
            if($del = @mysql_query($delete, $this->mysql_link)){
            	array_push($this->result,mysql_affected_rows($this->mysql_link));
                $this->myQuery = $delete; // Pass back the SQL
                return true; // The query exectued correctly
            }else{
            	array_push($this->result,mysql_error($this->mysql_link));
               	return false; // The query did not execute correctly
            }
        }else{
            return false; // The table does not exist
        }
    }
	
	// Function to update row in database
    public function update($table,$params=array(),$where){
        $this->checkConnection();
    	// Check to see if table exists
    	if($this->tableExists($table)){
    		// Create Array to hold all the columns to update
            $args=array();
			foreach($params as $field=>$value){
				// Seperate each column out with it's corresponding value
				$args[]=$field.'="'.$value.'"';
			}
			// Create the query
			$sql='UPDATE '.$table.' SET '.implode(',',$args).' WHERE '.$where;
			// Make query to database
            $this->myQuery = $sql; // Pass back the SQL
            if($query = @mysql_query($sql, $this->mysql_link)){
            	array_push($this->result,mysql_affected_rows($this->mysql_link));
            	return true; // Update has been successful
            }else{
            	array_push($this->result,mysql_error($this->mysql_link));
                return false; // Update has not been successful
            }
        }else{
            return false; // The table does not exist
        }
    }
    
    /**
     * Return a list of columns for a given table
     * @param  String $table 
     * @return array
     */
    public function getFields($table) {
        if($this->tableExists($table)){
            $result = @mysql_query("SELECT * FROM $table", $this->mysql_link);
            $myArr = array();
            $i = 0;
            while ($i < mysql_num_fields($result) ) {
                $val = mysql_fetch_field($result, $i); 
                array_push($myArr, $val->name);
                $i++;
             } 
            return $myArr;
        } else {
            return array();
        }
    }

    // Public function to check if table exists for use with queries
    public function tableExists($table){
        $tablesInDb = @mysql_query('SHOW TABLES FROM '.$this->glob['db_name'].' LIKE "'.$table.'"', $this->mysql_link);
        if($tablesInDb){
            if(mysql_num_rows($tablesInDb)==1){
                return true; // The table exists
            }else{
                array_push($this->result,$table." does not exist in this database");
                return false; // The table does not exist
            }
        }
    }
    
    // Public function to return the data to the user
    public function getResult(){
        $val = $this->result;
        $this->result = array();
        return $val;
    }

    //Pass the SQL back for debugging
    public function getSql(){
        $val = $this->myQuery;
        return $val;
    }

    //Pass the number of rows back
    public function numRows(){
        $val = $this->numResults;
        return $val;
    }

}