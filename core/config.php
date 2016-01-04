<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ( $_SERVER['SERVER_NAME'] == 'PHP-MVC.dev') {
	$env = 'development';
}

$json = json_decode(file_get_contents( '../database.json' ), true);
$dbhost = isset($json[$env]["host"]) ? $json[$env]["host"] : "";
$dbhost = isset($json[$env]["port"]) ? $dbhost.":".$json[$env]["port"] : $dbhost;
$dbuser = isset($json[$env]["user"]) ? $json[$env]["user"] : "";
$dbpass = isset($json[$env]["password"]) ? $json[$env]["password"] : "";
$dbname = isset($json[$env]["database"]) ? $json[$env]["database"] : "";

$dbCreds = array(
	"db_host" => $dbhost,
	"db_user" => $dbuser,
	"db_pass" => $dbpass,
	"db_name" => $dbname
);