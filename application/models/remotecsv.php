<?php
class RemoteCSV extends model {

    static    $fieldList        = [],
              $headers          = [];
     
    function __construct($id = null, $data = null) {
        parent::__construct($id, $data);
    }

    /**
     * Gets a CSV files from an sFTP location and adds to model
     * @param  string $query 
     * @return null
     */
    public function save() {
        try {

            // Some of these files could be quite large, 30 minutes should be more than plenty!
            set_time_limit(1800);
            
            $filename = '../cache/temp-file-'.date('Y-m-d-H-i-s').'.csv';
            $this->getFileFromSftp($filename);

            if ( $this->errorno === 0 ) {

                // Get the column names from the table
                self::$fieldList = $this->fetchFields();

                if ( $this->truncate ) {
                    $this->truncate();
                }

                $success = $this->file_get_contents_chunked($filename, 2048, function($chunk, &$handle, $iteration) {

                    if ( $iteration === 0 ) {
                        // We have some headers
                        foreach ($chunk as $value) {
                            self::$headers[] = $value;
                        }
                    }

                    if ( $iteration !== 0 ) {
                        // This is not the header so we can assume that we can add this chunk
                        // map headers to the key of the $chunk array
                        $mappedDataLine = $this->mapArr($chunk, self::$headers);
                        // We are going to map our data with our columns for easy inserting into DB
                        $this->data = $this->mapArr($this->removeColumns($mappedDataLine, $this->unusedColumns), self::$fieldList );
                        if ( count($this->data) > 0 ) {
                            $this->insertUpdate($this->toArray(), true);
                        }
                    }

                }, $this->delim);

                if ( !$success ) {
                    //It Failed
                    $this->sendError("Could not retreive the $this->folder.$this->filename file.");
                    $this->deleteFile($filename);                
                    return false;
                }

                // If we have got this far we can assume that everything has gone well
                $this->deleteFile($filename);                
                return true;
                exit;

            } else if ( $this->errorno === 78 ) {
                // We cannot find the file
                $this->sendError("Could not retreive the $this->filename file.");
            } else {
                // We can assume that we do not have a file
                $this->sendError("Tried to get $this->filename file and it failed.");
            }
            $this->deleteFile($filename);                
            return false;

        } catch (Exception $e) {
            $this->sendError("Something unexpected has happened - ".$e->getMessage());
        }
    }
}