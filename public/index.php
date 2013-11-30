<?php   
 
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));

 // Our index.php basically sets the $url variable and calls bootstrap.php which resides in our library directory.
$url = $_GET['url'];

require_once (ROOT . DS . 'library' . DS . 'bootstrap.php');