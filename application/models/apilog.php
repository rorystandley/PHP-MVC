<?php
class APILog extends model {

	protected $tableName        = 'api_log',
              $primaryKey       = 'id';
     
    function __construct($id = null, $data = null) {
		parent::__construct($id, $data);
    }

    /**
     * Create a log for the incoming API call
     * @param  string $username 
     * @param  string $data     
     * @param  string $method   
     * @param  string $path     
     * @param  string $headers  
     * @return integer           Row id for the log
     */
    public function create($username = '', $data = '', $method = '', $path = '', $headers = '') {
        $this->db->insert($this->tableName, array(
            'created' => date('Y-m-d H:i:s'),
            'log_account' => $username,
            'log_http_method' => $method,
            'log_request_path' => $path,
            'log_headers' => $headers,
            'log_input' => mysql_real_escape_string($data)
        ));

        $result = $this->db->getResult();

        if ( isset($result[0]) ) {
            return $result[0];
        }

        // TODO - Handle this correctly
        return 0;
    }

    /**
     * Update an existing log
     * @param  integer $id       Row id for the log
     * @param  string  $code     
     * @param  string  $response 
     * @return null            
     */
    public function update($id = 0, $code = '', $response = '') {
        $this->db->update($this->tableName, array(
            'log_result_code' => $code,
            'log_output_content' => mysql_real_escape_string($response)
        ), 'id = '.$id);
    }
}