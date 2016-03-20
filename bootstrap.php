<?php
define('ONELEVEL', dirname(__FILE__));
require dirname(__FILE__).'/vendor/autoload.php';

// Lets add in our .env magic
$dotenv = new Dotenv\Dotenv(ONELEVEL);
// We need specific values in the file, so lets check we have them
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_PORT', 'DB_USER', 'DB_PASS']);
$dotenv->required('DB_NAME')->notEmpty();

spl_autoload_register(function($className) {
	if ( file_exists( ONELEVEL.'/application/controllers/' . ucfirst($className) . '.php' ) ) {
        include_once( ONELEVEL.'/application/controllers/' . ucfirst($className) . '.php' );
    } else if ( file_exists( ONELEVEL.'/application/models/' . ucfirst($className) . '.php' ) ) {
        include_once( ONELEVEL.'/application/models/' . ucfirst($className) . '.php' );
    } else if ( file_exists( ONELEVEL.'/core/' . ucfirst($className) . '.class.php' ) ) {
        include_once( ONELEVEL.'/core/' . ucfirst($className) . '.class.php' );
    } else {
        /* Error Generation Code Here */
        include_once( ONELEVEL.'/application/controllers/ErrorController.php' );
        /* Error Generation Code Here */
        $dispatch = new ErrorController;
        call_user_func_array(array($dispatch, 'notFound'), []);
        exit;
    }
});