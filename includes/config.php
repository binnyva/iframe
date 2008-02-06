<?php
set_include_path(get_include_path() . PATH_SEPARATOR . $config['iframe_folder'] . 'includes/classes'); //Make including classes easier

$config['PHP_SELF'] = ($PHP_SELF) ? $PHP_SELF : $_SERVER["PHP_SELF"];

if(!isset($config['site_absolute_path'])) {
	$path = dirname($config['PHP_SELF']);
	//Go up until the correct path is found
	while (strlen($path) > 2) {
		if(file_exists($_SERVER["DOCUMENT_ROOT"] . $path . DIRECTORY_SEPARATOR . 'configuration.php')) break;
		else $path = dirname($path);
	}
	$config['site_absolute_path'] = $path . DIRECTORY_SEPARATOR;
}

if(!isset($config['site_url'])) {
	$config['site_url']	= "http://" . $_SERVER['HTTP_HOST'] . $config['site_absolute_path'];
}

/**
 * The current mode of the system. This will affect how errors will be shown
 *  d = Development Mode
 *	t = Testing Mode
 *	p = Production Mode
 */
if(!isset($config['mode'])) $config['mode']	= 'd'; //Default Config Mode

if($config['mode'] == 'd') error_reporting(E_ALL);
elseif($config['mode'] == 'p') error_reporting(0);

// Database connection is optional
$sql = false;
if(isset($config['db_host']) and $config['db_host']) {
	$sql = new Sql($config['db_host'],$config['db_user'],$config['db_password'],$config['db_database']); // Connect to DB
	Sql::$mode = $config['mode'];
}
$template = new MVC;

//Otherways it is a mess with google
ini_set('url_rewriter.tags',"");
ini_set('session.use_trans_sid',false); 
session_start();

$date_format = '%d %b, %Y';
$abs = $config['site_absolute_path']; //The final '/' is a must
$config['code_path'] = preg_replace("/includes/",'',dirname(__FILE__));
