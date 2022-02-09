<?php

/**
 * Author: Kwame Oteng Appiah-Nti
 * Author URI: http://twitter.com/developerkwame
 * Author Email: koteng@wigal.com.gh
 * 
 * Redde Functions:
 * Functions to assist plugin
 */

//Start Session
function init_session() 
{
 	@session_start();
}

//Remove Session
function remove_session($session_item = array())
{
	if($session_item != null) {
		foreach($session_item as $item) {
			unset($_SESSION[$item]);
		}
	} 
}

function find_wordpress_base_path($file_name) {
	$dir = dirname(__FILE__);
	do {
		//it is possible to check for other files here
		$file = $dir."/". $file_name .".php";
		if( file_exists($file) ) {
			return $dir;
		}
	} while( $dir = realpath("$dir/..") );
	return null;
}

function load_wp_file_from_base_path($file_name) {
	require_once(dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/". $file_name . ".php");
}
