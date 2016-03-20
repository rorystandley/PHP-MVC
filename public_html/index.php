<?php
session_start();
require '../bootstrap.php';

if ( getenv('SHOWERRORS') == 'true' ) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
}

// Lets set out timeout
ini_set('max_execution_time', getenv('DB_HOST'));
// Now lets add in our application
include("../core/application.php");