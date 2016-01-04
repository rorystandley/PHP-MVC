<?php
class Model {

	protected $db,
			  $data;

	function __construct($id = null, $data = array()) {
		// Load database
		$this->db  = new Database;
		$this->db->connect();

		if(!is_null($id)) {
			$this->find($id);
		}

		if (!empty($data)) {
			$this->data = $data;
		} elseif(!is_object($this->data)) {
			$this->data = new stdClass;
		}
	}

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
		return $this->data->{$this->primaryKey};
	}

	/**
	 * find
	 * @desc	Find a model by primary key.
	 * @param	int	$id
	 * @return	\static
	 * @throws	Exception
	 */
	public function find($id = 0) {
		if(!$this->db->tableExists($this->tableName)) {
			throw new Exception('Not Found', 404);
		}

		$this->db->select($this->tableName, '*', null, "$this->primaryKey = '$id'", null, 'OBJECT');

		$this->data = $this->db->getResult();

		if(is_null($this->data)) {
			throw new Exception('Not Found', 404);
		}

		return new static(null, $this->data);
	}

	/**
	 * everything from a model
	 * @desc	Return all rows for the specific model.
	 * @param  array  $columns
	 * @return	array
	 */
	public function everything($columns = array(), $order = '') {
		$columnNames = "*";
		// Check to see if we have column names to add to our query
		if ( count($columns) > 0 ) {
			$columnNames = implode(",", $columns);
		}

		$orderBy = strlen($order) > 0 ? '1 = 1 ORDER BY '.$order : '';

		$this->db->select($this->tableName, $columnNames, null, $orderBy);

		return $this->db->getResult();
	}

	/**
	 * Get everything by the current day
	 * @param  array  $columns [description]
	 * @return [type]          [description]
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
		return new static(null, $this->db->getResult());
	}

	/**
	 * Extends the database insert method
	 * @param  array  $data 
	 * @return booleane
	 */
	public function save($data = array()) {
		$data = array_merge((array) $this->data, $data);

		$data['created'] = date('Y-m-d H:i:s');
		$data['updated'] = date('Y-m-d H:i:s');

		// we are inserting, make sure no primary key is set
		if(isset($data[$this->primaryKey])) {
			unset($data[$this->primaryKey]);
		}

		$save = $this->db->insert($this->tableName, $data);

		// TODO this needs to be worked on with the current Database
		// $id = $this->db->insert_id();
		// $model = $this->find($id);

		// $this->data = $model->data;

		return $save;
	}

	public function update($data = array(), $merge = true) {
		$data = $merge === true ? array_merge((array) $this->data, $data) : $data;

		$data['updated'] = date('Y-m-d H:i:s');

		// make sure the primary key is not updated
		if(isset($data[$this->primaryKey])) {
			unset($data[$this->primaryKey]);
		}

		$id = $this->getId();
		$update = $this->db->update($this->tableName, $data, $this->primaryKey . " = " . "'$id'");

		// now make sure we have the latest data
		// $this->data = (object) array_merge((array) $this->data, $data);

		return $update;
	}

	/**
	 * toArray
	 * @desc	Return the data values as an array.
	 * @return	array
	 */
	public function toArray() {
		$data = (array) $this->get_data();

		// remove any hidden values
		if ( isset($this->hidden) ) {
			foreach($this->hidden as $key) {
				if(array_key_exists($key, $data)) {
					unset($data[$key]);
				}
			}
		}
		
		return $data;
	}

	/**
	 * get_data
	 * @desc	Get the data object.
	 * @return	object
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * truncate by models table name
	 * @return null 
	 */
	public function truncate() {
		if($this->db->tableExists($this->tableName)) {
			$this->db->sql("TRUNCATE TABLE $this->tableName");
		}
	}

	public function curlGet($action = "") {
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  	CURLOPT_URL => $this->endpoint.$action,
		  	CURLOPT_RETURNTRANSFER => true,
		  	CURLOPT_SSL_VERIFYHOST => false,
		  	CURLOPT_SSL_VERIFYPEER => false,
		  	CURLOPT_ENCODING => "",
		  	CURLOPT_MAXREDIRS => 10,
		  	CURLOPT_TIMEOUT => 1200,
		  	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  	CURLOPT_CUSTOMREQUEST => "GET",
		  	CURLOPT_HTTPHEADER => array(
		    	"cache-control: no-cache"
		  	),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			$this->sendError($err . "<p>The action was - $action</p>");
			return false;
		} else {
		  	return $response;
		}

	}

	/**
	 * Fetch the fields of a model. You can also remove fields from the returned array
	 * before passing back
	 * @param  array  $removeColumns these are the fields to be removed from the returned array
	 * @return array                
	 */
	public function fetchFields($removeColumns =  array())  {
		$result = $this->db->getFields($this->tableName);
		return $result;
	}

	/**
	 * Specifically for Fishbowl at the moment, we need to remove some columns from the data we get and 
	 * store
	 * @param  array  $arr           original array
	 * @param  array  $removeColumns array of columns to remove
	 * @return array
	 */
	public function removeColumns($arr = array(), $removeColumns = array()) {
		if ( count($removeColumns) > 0 ) {
			// We need to remove the columns from the result
			foreach ($removeColumns as $value) {
				unset($arr[$value]);
			}
		}
		return array_values($arr);
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
				$myArr[$value] = $arr1[$key];
			}
		}
		return $myArr;
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
	 * Send error emails out
	 * @param  string $msg  
	 */
	public function sendError($msg = '') {

		$to = "rorystandley@gmail.com";
		$subject = "Data API Error [$_SERVER[SERVER_NAME]]";

		$headers = "From: Rory Standley <rorystandley@gmail.com>\r\n";
		$headers .= "Reply-To: rorystandley@gmail.com\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

		$message = '<html><body>';
		$message .= '<h1>Something has gone wrong</h1>';
		$message .= $msg;
		$message .= '</body></html>';

		mail($to, $subject, $message, $headers);
	}

}