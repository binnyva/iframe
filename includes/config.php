<?php
//Find the location of the root folder
$path = ($PHP_SELF) ? $PHP_SELF : $_SERVER["PHP_SELF"];
while (strlen($path) > 2) {
	if(file_exists($_SERVER["DOCUMENT_ROOT"]."$path/configuration.php")) {
		break;
	} else {
		$path = dirname($path);
	}
}
$url = "http://" . $_SERVER['HTTP_HOST'] . $path.'/';

$config['site_url']		= $url;
$config['site_path']	= $path;

/**	
 * The current mode of the system. This will affect how errors will be shown
 *  d = Development Mode
 *	t = Testing Mode
 *	p = Production Mode
 */
if(!isset($config['mode'])) $config['mode']			= 'd'; //Default Config Mode

if($config['mode'] == 'd') {
	error_reporting(E_ALL);
} elseif($config['mode'] == 'p') {
	error_reporting(0);
}

// Database connection is optional
$sql = false;
if(isset($config['db_host']) and $config['db_host']) {
	// Connect to DB
	$sql = new Sql($config['db_host'],$config['db_user'],$config['db_password'],$config['db_database']);
	Sql::$mode = $config['mode'];
}

//Otherways it is a mess with google
ini_set('url_rewriter.tags',"");
ini_set('session.use_trans_sid',false); 
session_start();

$date_format = '%d %b, %Y';
$abs = $config['absolute_path']; //The final '/' is a must
$config['code_path'] = preg_replace("/includes/",'',dirname(__FILE__));