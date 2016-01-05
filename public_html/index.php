<?php
require '../vendor/autoload.php';
// Lets add in our .env magic
$dotenv = new Dotenv\Dotenv('../');
// We need specific values in the file, so lets check we have them
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_PORT', 'DB_USER', 'DB_PASS']);
$dotenv->required('DB_NAME')->notEmpty();
// Now lets add in our application
include("../core/application.php");