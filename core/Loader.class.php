<?php
class Loader {

	/**
	 * Custom helper functions can now be written
	 * @param  array  $helpers list of helpers that you would like to load
	 * @return null          
	 */
    function helper($helpers = array()) {
    	foreach ($helpers as $helper) {
    		if ( file_exists('../helpers/'.$helper.'.php') ) {
				include_once('../helpers/'.$helper.'.php');
			}
    	}

    	return $this;
    }
}