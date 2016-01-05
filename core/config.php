<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$dbCreds = array(
	"db_host" => getenv('DB_HOST'),
	"db_user" => getenv('DB_USER'),
	"db_pass" => getenv('DB_PASS'),
	"db_name" => getenv('DB_NAME')
);