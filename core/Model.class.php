<?php
class Model {

	protected 	$db,
				$data,
				$tableName = '',
				$primaryKey = 'id',
				$statusFlag = 'sent',
				$lockColumn = 'lock_me',
				$endpoint,
				$username,
				$password,
				$url,
				$port,
				$folder,
				$filename,
				$errorno = 0,
				$saveFilename,
				$redirectPath,
				$loginPath,
				$truncate = false,
				$info = [],
				$unusedColumns = [],
				$delim = ',';

	function __construct($id = null, $data = array()) {
		// Load database
		$this->db  = new Database;
		$this->db->connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'), getenv('DB_PORT'));
		
		// Load loader
		$this->loader = new Loader;

		if(!is_null($id)) {
			$this->find($id);
		}

		if (!empty($data)) {
			$this->data = (object)$data;
		} elseif(!is_object($this->data)) {
			$this->data = new stdClass;
		}
	}

	/**
	 * get a value for this object; return false if key does not exist
	 * @param  string $key Get the value for this variable
	 * @return boolean/string      If the variable exists it will return the value; otherwise it will return false
	 */
	public function __get($key) {
		if(method_exists($this, 'get_' . $key)) {
			return $this->{'get_' . $key}();
		}
		elseif(isset($this->data->{$key})) {
			return $this->data->{$key};
		}
		elseif(isset($this->{$key})) {
			return $this->{$key};
		}
		
		return false;
	}

	/**
	 * Set a value for this object
	 * @param string $key   
	 * @param boolean/string $value 
	 */
	public function __set($key, $value) {
		if(method_exists($this, 'set_' . $key)) {
			$this->{'set_' . $key}($value);
		}
		elseif(property_exists($this, '_' . $key)) {
			$this->{'_' . $key} = $value;
		}
		elseif(property_exists($this, $key)) {
			$this->{$key} = $value;
		}
		else {
			if(!is_object($this->data)) {
				$this->data = new stdClass;
			}
			$this->data->{$key} = $value;
		}
	}

	/**
	 * Check to see if a value isset
	 * @param  string  $key 
	 * @return boolean      
	 */
	public function __isset($key) {
		if(method_exists($this, 'isset_' . $key)) {
			$value = $this->{'isset_' . $key}();
		}
		elseif(method_exists($this, 'get_' . $key)) {
			$value = $this->{'get_' . $key}();
		}
		elseif(property_exists($this, '_' . $key)) {
			$value = $this->{'_' . $key};
		}
		elseif(property_exists($this, $key)) {
			$value = $this->{$key};
		}
		elseif(isset($this->data) && isset($this->data->{$key})) {
			$value = $this->data->{$key};
		}

		return !empty($value) ? true : false;
	}

	/**
	 * get_id
	 * @desc	Return the primary key for the model, always return as an integer.
	 * @return	int
	 */
	public function getId() {
		return isset($this->data->{$this->primaryKey}) ? $this->data->{$this->primaryKey} : 0;
	}

	/**
	 * find
	 * @desc	Find a model by primary key.
	 * @param	int	$id
	 * @return	\static
	 * @throws	Exception
	 */
	public function find($id = 0) {

		try {
			
			if(!$this->db->tableExists($this->tableName)) {
				return new static();
			}
		

			$this->db->select($this->tableName, '*', null, "$this->primaryKey = '$id'", null, 'OBJECT');

			$this->data = $this->db->getResult();

			if(is_null($this->data)) {
				return new static();
			}

			return new static(null, $this->data);

		} catch(Exception $e) {
  			return new static();
		}
	}

	/**
	 * everything from a model
	 * @desc	Return all rows for the specific model.
	 * @param  array  $columns
	 * @param  string  $order
	 * @param  string  $date
	 * @return	array
	 */
	public function everything($columns = array(), $order = '', $date = null) {
		$columnNames = "*";
		// Check to see if we have column names to add to our query
		if ( count($columns) > 0 ) {
			$columnNames = implode(",", $columns);
		}

		$where = '1 = 1 ';
		$where .= $date !== null ? 'AND DATE(updated) = "'.$date.'"' : '';
		$where .= strlen($order) > 0 ? ' ORDER BY '.$order : '';

		$this->db->select($this->tableName, $columnNames, null, $where);

		if ( $this->db->numRows() === 1 ) {
			return array($this->db->getResult());
		}

		return $this->db->getResult();
	}

	/**
	 * Get random rows from a model
	 * @param  integer $number number of rows to return in random order
	 * @return array
	 */
	public function getRandomRows($number = 200) {
		$this->db->select($this->tableName, "*", null, " 1 = 1 AND $this->statusFlag = 0 ORDER BY RAND() LIMIT $number");

		if ( $this->db->numRows() === 1 ) {
			return array($this->db->getResult());
		}

		return $this->db->getResult();
	}

	/**
	 * Lock a row from a model
	 * @param  integer $id id of row
	 * @return boolean      
	 */
	public function lock($id = 0) {
		return $this->db->update($this->tableName, array($this->lockColumn => uniqid()) , $this->primaryKey. ' = '.$id);		
	}

	/**
	 * Unlock a row from a model
	 * TODO - Requires Unit Testing
	 * @param  integer $id 
	 * @return boolean
	 */
	public function unlock($id = 0) {
		return $this->db->update($this->tableName, array($this->lockColumn => null) , $this->primaryKey. ' = '.$id);
	}

	/**
	 * Update a rows status flag 
	 * TODO - Requires Unit Testing
	 * @param  integer $id   row id
	 * @param  integer $flag what to set the flag to
	 * @return null        
	 */
	public function updateSentFlag($id = 0, $flag = 0) {
		$this->db->update($this->tableName, [$this->statusFlag => $flag], $this->primaryKey . ' = "' .$id.'"');
		$this->db->getResult();
	}

	/**
	 * Get everything by the current day
	 * @param  array  $columns 
	 * @return array         
	 */
	public function today($columns = array()) {
		$columnNames = "*";
		// Check to see if we have column names to add to our query
		if ( count($columns) > 0 ) {
			$columnNames = implode(",", $columns);
		}

		$this->db->select($this->tableName, $columnNames, null, 'DATE(created) = CURDATE()');
		return $this->db->getResult();
	}

	/**
	 * Get the latest row from the model by primary key
	 * @param  string $order desc or asc
	 * @return Static Object
	 */
	public function latest($order = 'desc') {
		$this->db->select($this->tableName, '*', null, null, "$this->primaryKey $order LIMIT 1", 'OBJECT');
		return $this->data = $this->db->getResult();
	}

	/**
	 * Extends the database insert method
	 * @param  array  $data 
	 * @return boolean
	 */
	public function insert($data = array(), $timestamps = false, $removePrimaryKey = true) {
		// make sure the primary key is not inserted
		if ( isset($data[$this->primaryKey]) && $removePrimaryKey ) {
			unset($data[$this->primaryKey]);
		}

		if ( isset($data['loader']) ) {
			unset($data['loader']);
		}

		if ( $timestamps ) {
			$data['created'] = date('Y-m-d H:i:s');
			$data['updated'] = date('Y-m-d H:i:s');
		}

		$insert = $this->db->insert($this->tableName, $data);
		
		return $insert;
	}

	/**
	 * Extends the database insertUpdate method
	 * @param  array   $data             
	 * @param  boolean $timestamps       
	 * @param  boolean $removePrimaryKey 
	 * @return int
	 */
	public function insertUpdate($data = array(), $timestamps = false, $removePrimaryKey = true, $sent = true) {
		// make sure the primary key is not inserted
		if ( isset($data[$this->primaryKey]) && $removePrimaryKey ) {
			unset($data[$this->primaryKey]);
		}

		if ( isset($data['loader']) ) {
			unset($data['loader']);
		}

		if ( $timestamps ) {
			$data['created'] = date('Y-m-d H:i:s');
			$data['updated'] = date('Y-m-d H:i:s');
		}

		if ( $sent ) {
			$data['sent'] = 0;
		}

		$insertUpdate = $this->db->insertUpdate($this->tableName, $data);
		
		return $insertUpdate;
	}

	public function update($data = array(), $merge = true) {
		$data = $merge === true ? array_merge((array) $this->data, $data) : $data;

		$data['updated'] = date('Y-m-d H:i:s');

		$id = isset($data[$this->primaryKey]) ? $data[$this->primaryKey] : $this->getId();

		// make sure the primary key is not updated
		if(isset($data[$this->primaryKey])) {
			unset($data[$this->primaryKey]);
		}

		if ( isset($data['loader']) ) {
			unset($data['loader']);
		}

		$update = $this->db->update($this->tableName, $data, $this->primaryKey . " = " . "'$id'");

		return $update;
	}

	/**
	 * toArray
	 * @desc	Return the data values as an array.
	 * @return	array
	 */
	public function toArray() {
		$data = (array) $this->getData();
		
		return $data;
	}

	/**
	 * get_data
	 * @desc	Get the data object.
	 * @return	object
	 */
	public function getdata() {
		return $this->data;
	}

	/**
	 * truncate by models table name
	 * @return boolean
	 */
	public function truncate() {
		return $this->db->truncateTable($this->tableName);
	}

	/**
	 * Fetch the fields of a model.
	 * @param array $hideColumns
	 * @return array                
	 */
	public function fetchFields($hideColumns = [])  {
		$myArr = ['sent', 'created', 'updated', $this->primaryKey];

		// Check to see if we need to append anymore columns
		if ( count($hideColumns) > 0 ) {
			foreach ($hideColumns as $column) {
				$myArr[] = $column;
			}
		}

		return $this->db->getFields($this->tableName, $myArr);
	}

	/**
	 * Make a GET with Curl
	 * TODO - Require Unit Testing
	 * @param  string $action appended to the endpoint
	 * @return response
	 */
	public function curlGet($action = "") {
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => trim($this->endpoint).trim($action),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 2400,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"cache-control: no-cache"
				),
			));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		$this->info = curl_getinfo($curl);

		curl_close($curl);

		if ($err) {
			$this->sendError($err . "<p>The action was - {$this->endpoint}$action</p>", $this->info);
			return false;
		} else {
			return $response;
		}

	}

	/**
	 * Make a POST with Curl
	 * Added the ability to change the type as well
	 * TODO - Requires Unit Testing
	 * @param  string $action appended to the endpoint
	 * @param  array  $data   data passed through to the endpoint
	 * @param  string $type   Ability to change request
	 * @return response
	 */
	public function curlPost($action = "", $data = array(), $type = 'POST', $headers = 'JSON', $showHeaders = false) {
		$curl = curl_init();

		switch($headers) {
			case 'form-data':
				$headerDetails = [
				    "Content-Type: multipart/form-data"
				];
				break;
			case 'JSON':
			default:
				$headerDetails = [
					"accept: application/json",
					"content-type: application/json",
				];
				break;
		}

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->endpoint.$action,
			CURLOPT_USERPWD => $this->username . ":" . $this->password,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 2400,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $type,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_ENCODING => "",
			CURLOPT_HEADER => $showHeaders,
			CURLOPT_HTTPHEADER => $headerDetails,
		));


		$response = curl_exec($curl);

		$err = curl_error($curl);
		$this->info = curl_getinfo($curl);

		curl_close($curl);

		if ($err) {
			$this->sendError($err . "<p>The action was - $action</p>", $this->info);
			return false;
		} else {
			return $response;
		}
	}

	/**
	 * Map two arrays together based on one having values that are required to be a key in the other
	 * @param  array  $arr1 
	 * @param  array  $arr2
	 * @return array       
	 */
	public function mapArr($arr1 = array(), $arr2 = array()) {
		$myArr = array();
		foreach ($arr2 as $key => $value) {
			if ( isset($arr2[$key]) && isset($arr1[$key]) ) {

				$value = strtolower($value);
				$value = str_replace(' ', '_', $value);
				$value = str_replace('-', '_', $value);

				$myArr[$value] = $arr1[$key];
			}
		}
		return $myArr;
	}

	/**
	 * Remove columns from any given array
	 * @param  array  $arr           original array
	 * @param  array  $removeColumns array of columns to remove
	 * @return array
	 */
	function removeColumns($arr = array(), $removeColumns = array()) {
		if ( count($removeColumns) > 0 ) {
			// We need to remove the columns from the result
			foreach ($removeColumns as $value) {
				unset($arr[$value]);
			}
		}
		return array_values($arr);
	}

	/**
	 * Redirect the user to a give slug
	 * @param  string $slug 
	 * @return null       
	 */
	public function redirect($slug = '/', $msg = '') {
		$msg = strlen($msg) > 0 ? '?msg='.$msg : '';
		header('Location: '.$slug.$msg);
	}

	/**
	 * Delete a given file
	 * @param  string $filename location and file name
	 * @return boolean           
	 */
	protected function deleteFile($filename = '') {
		if ( !unlink($filename) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get a file from an sFTP location
	 * @return String 
	 */
	protected function getFileFromSftp($filename = '') {
		try {

			$url = sprintf('sftp://%s:%s@%s:%s%s%s', $this->username, $this->password, $this->url, $this->port, $this->folder, $this->filename);

			$ch	 = curl_init();
			$fp = fopen($filename, "w");

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, false);

			$data			= curl_exec($ch);
			$this->info 	= curl_getinfo($ch);
			$this->errorno	= curl_errno($ch);

		} catch (Exception $e) {
			$this->sendError("Something unexpected has happened - ".$e->getMessage());
		}		
	}

	/**
	 * Send error emails out
	 * @param  string $msg  
	 */
	public function sendError($msg = '', $data = null) {
		ini_set('SMTP', getenv('SMTP_URL'));

		$to = getenv('EREMAIL_TO');
		$subject = getenv('EREMAIL_SUBJECT');

		$headers = "From: ".getenv('EREMAIL_FROM_NAME')." <".getenv('EREMAIL_FROM_EMAIL').">\r\n";
		$headers .= "Reply-To: ".getenv('EREMAIL_FROM_EMAIL')."\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

		$message = '<html><body>';
		$message .= '<h1>Something has gone wrong</h1>';
		$message .= $msg;

		if ( $data !== null ) {
			$message .= '<pre>';
			$message .= print_r($data, true);
			$message .= '</pre>';
		}
		
		$message .= '</body></html>';

		mail($to, $subject, $message, $headers);
	}

	/**
	 * Chunk a given file
	 * http://stackoverflow.com/questions/5249279/file-get-contents-php-fatal-error-allowed-memory-exhausted/5249971#5249971
	 * @param  string $file       
	 * @param  int $chunk_size 
	 * @param  method $callback   
	 * @return boolean     
	 */
	public function file_get_contents_chunked($file, $chunk_size, $callback, $delim = ',') {
		try {

			$handle = fopen($file, "r");

			if ( $handle ) {
				$i = 0;
				while (!feof($handle)) {
					call_user_func_array(
						$callback, 
						array( 
							fgetcsv($handle, $chunk_size, $delim), 
							&$handle, 
							$i 
							) 
						);
					$i++;
				}
				fclose($handle);
				return true;
			} else {
				return false;
			}


		} catch(Exception $e) {
			$this->sendError("file_get_contents_chunked - ".$e->getMessage());
			return false;
		}

		return true;
	}

    /**
     * Create a table with a given tableName and columns
     * @param  string $tableName name of the table to create
     * @param  array  $columns   an array of columns and their types
     * @return boolean
     */
    public function createTable($tableName = '', $columns = []) {
    	$value = $this->db->createTable($tableName, $columns);
    	return $value;
    }

    /**
     * Drop a table by a given tableName
     * @param  string $tableName name of the table to drop
     * @return boolean            
     */
    public function dropTable($tableName = '') {
    	$value = $this->db->dropTable($tableName);
    	$this->db->getResult();
    	return $value;
    }

    /**
     * Return the count of a given model
     * @return int number of rows in the model
     */
    public function count() {
    	$this->db->select($this->tableName);
    	$this->db->getResult();
    	// $result = $this->db->getResult();
    	return $this->db->numRows();
    }

	public function getContents($url = '')
	{
        $this->endpoint = $url;
        $html = $this->curlGet();
        $dom = new DOMDocument;

        try {
            return @$dom->loadHTML($html);
        } catch (Exception $e) {
            $this->sendError("getContents - ".$e->getMessage());
            return false;
        }
	}
}