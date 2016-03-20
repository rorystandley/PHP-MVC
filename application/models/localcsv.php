<?php
class LocalCSV extends model {

    protected  $headers     = [],
               $folder      = '../cache/';
    static  $fieldList  = [];
     
    function __construct($id = null, $data = null) {
        parent::__construct($id, $data);
    }

    /**
     * Save CSV data from local location
     * @param  string $query 
     * @return null
     */
    public function save($query = '') {
        try {

            // Some of these files could be quite large, 30 minutes should be more than plenty!
            set_time_limit(1800);

            if ( $this->truncate ) {
                $this->truncate();
            }

            // Get the column names from the table
            self::$fieldList = $this->fetchFields();

            $success = $this->file_get_contents_chunked($this->folder.$this->filename, 1024, function($chunk, &$handle, $iteration) {

                if ( $iteration === 0 ) {
                    // We have some headers
                    foreach ($chunk as $value) {
                        self::$headers[] = $value;
                    }
                }

                if ( $iteration !== 0 ) {
                    // This is not the header so we can assume that we can add this chunk
                    // map headers to the key of the $chunk array
                    $mappedDataLine = $this->removeColumns( $this->mapArr($chunk, $this->headers), $this->unusedColumns);
                    // We are going to map our data with our columns for easy inserting into DB
                    $this->data = $this->mapArr($this->removeColumns($mappedDataLine, $this->unusedColumns), self::$fieldList );
                    if ( count($this->data) > 0 ) {
                        $result = $this->insertUpdate($this->toArray(), true);

                        if ( !$result ) {
                            $myData = [
                                'result' => $this->db->getResult(),
                                'sql' => $this->db->getSql(),
                                'data' => $this->data,
                                'table' => $this->tableNames
                            ];
                            $this->sendError("Row has not been added correctly - ", $myData);
                        }

                        // CLearing out the results (Little hack)
                        $this->db->getResult();
                    }
                }

            }, $this->delim);

            if ( !$success ) {
                //It Failed
                $this->sendError("Could not retreive the $this->folder$this->filename file.");
                return false;
            }

            // If we have got this far we can assume that everything has gone well
            return true;

        } catch (Exception $e) {
            $this->sendError("Something unexpected has happened - ".$e->getMessage());
            return false;
        }
    }
}