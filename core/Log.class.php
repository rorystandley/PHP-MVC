<?php

class Log {

    /**
     * Write a string to our chosen log file
     * @param  string $type type of log file
     * @param  string $name name of the task to log
     * @return [type]       [description]
     */
    static function write($type = 'send', $name = '') {
        $date = self::today();
        $time = date("H:i:s");

        if ( !self::checkFileexists($type) ) {
            // We need to create the file 
            self::createLogFile($type);
        }

        file_put_contents( "../logs/$type/$type-$date.txt", "$name \t- $time\r\n", FILE_APPEND);
    }

    /**
     * Check to see if a log file exists
     * @param  string $type type of log file
     * @return boolean
     */
    static function checkFileExists($type = '') {
        $date = self::today();
        if ( file_exists("../logs/$type/$type-$date.txt") ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create a log file
     * @param  string $type type of log fiel to create
     * @return null       
     */
    static function createLogFile($type = '') {
        $date = self::today();
        $file = fopen("../logs/$type/$type-$date.txt", "w");
        fclose($file);
    }

    /**
     * Get todays date
     * @return string 
     */
    static function today() {
        $date = new DateTime();
        $date->add(DateInterval::createFromDateString('today'));
        return $date->format('Y-m-d');
    }
	
}
