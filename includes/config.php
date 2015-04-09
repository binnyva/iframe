<?php
//Yes, I know this file is a mess. I'll get around to fixing it. Someday...

//Make including classes easier
set_include_path(get_include_path() 
	. PATH_SEPARATOR . joinPath($config['iframe_folder'], 'includes/classes')
	. PATH_SEPARATOR . joinPath($config['iframe_folder'], 'includes/classes/external')
	); 
if(file_exists(joinPath($config['site_folder'] , 'models'))) set_include_path(get_include_path() . PATH_SEPARATOR .  joinPath($config['site_folder'] , 'models'));

//Find all path info
$config['PHP_SELF'] = !empty($PHP_SELF) ? $PHP_SELF : $_SERVER["PHP_SELF"];

//Absolute Path
if(!isset($config['site_absolute_path'])) {
	$path = dirname($config['PHP_SELF']);
	//Go up until the correct path is found
	while (strlen($path) > 2) {
		if(file_exists($_SERVER["DOCUMENT_ROOT"] . $path . DIRECTORY_SEPARATOR . 'configuration.php')) break;
		else $path = dirname($path);
	}
	$config['site_absolute_path'] = str_replace('//','/', $path . DIRECTORY_SEPARATOR);
}
$config['current_page'] = str_replace($config['site_absolute_path'], '/', $config['PHP_SELF']);

// This is to tell us where the App is CLI, is hosted online or on our own system, etc.
$config['server_host'] = 'cli';
$config['server_online'] = true;
if(isset($_SERVER['HTTP_HOST'])) {
	$config['server_host'] = $_SERVER['HTTP_HOST'];
	if($config['server_host'] == 'localhost' 
		or $config['server_host'] == '127.0.0.1'
		or $config['server_host'] == $_SERVER['SERVER_ADDR']) {
			$config['server_online'] = false;
	}
}
if($config['server_host'] == 'cli') $config['server_online'] = false;

// Get the full URL of the website.
if(!isset($config['site_url']) and isset($_SERVER['HTTP_HOST'])) {
	$config['site_url']	= "http://" . $_SERVER['HTTP_HOST'] . $config['site_absolute_path'];
}

/**
 * The current mode of the system. This will affect how errors will be shown
 *  d = Development Mode
 *	t = Testing Mode
 *	p = Production Mode
 */
if(!isset($config['mode'])) $config['mode']	= 'd'; //Default Config Mode

if($config['mode'] == 'd') {
	error_reporting(E_ALL);
	
	$Logger = false;
	if(i($QUERY,'debug') == 'log') {
		include(joinPath("Development", "Logger.php"));
		$Logger = new Logger;
		$Logger->log("\nRendering Request: $_SERVER[REQUEST_URI]");
	}
}
elseif($config['mode'] == 'p') error_reporting(0);

// Database connection is optional
$sql = false;
if(isset($config['db_host']) and $config['db_host']) {
	$sql = new Sql($config['db_host'], $config['db_user'], $config['db_password'], $config['db_database']); // Connect to DB
	Sql::$mode = $config['mode'];
}
if(!isset($config['use_mvc']) or $config['use_mvc'] === false) $template = new MVC;

//Otherways it is a mess with google
ini_set('url_rewriter.tags',"");
ini_set('session.use_trans_sid',false); 
if(isset($_SERVER["HTTP_HOST"])) session_start(); //Don't start the session for a console app.

// Single user config option enabled. Login functions are not required.
if(isset($config['single_user']) and $config['single_user']) {
	$_SESSION['user_id'] = $config['single_user'];
}

// Default Date and time formats.
$config['date_format']	= '%D %b %Y';
$config['time_format']	= '%D %b %Y, %h:%i %p';

$config['date_format_php']	= phpDateFormat($config['date_format']);
$config['time_format_php']	= phpDateFormat($config['time_format']);

$abs = $config['site_absolute_path']; // :DEPRECIATED:

$config['code_path'] = preg_replace("/includes/",'',dirname(__FILE__));
if(isset($config['site_url']) and !isset($config['site_home'])) {
	$config['home_url'] = $config['site_url'];
	$config['site_home'] = $config['site_url'];
}

//Auto-include the application.php file
if(isset($config['site_relative_path']) and file_exists($config['site_relative_path'] . 'includes/application.php')) {
	require($config['site_relative_path'] . 'includes/application.php');
}

// Plugin System
$i_plugin = false;
if(file_exists(joinPath($config['site_folder'],'plugins'))) {
	$i_plugin = new Plugin(joinPath($config['site_folder'],'plugins'));

	$i_plugin->callHook('init');
}
