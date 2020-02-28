<?php
namespace iframe;

class App {
	public static $config;
	public static $db;
	public static $template;

	function __construct()
	{
		$this->bootstrap();
		$this->setupBackwardCompatibility();
	}

	public function render($__template_file = "", $use_layout = true, $use_exact_path = false, $variable_array = false)
	{
		static::$template->render($__template_file, $use_layout, $use_exact_path, $variable_array);
	}

	public function setupBackwardCompatibility()
	{
		$config = static::$config;

		$config['app_title'] = $config['app_name'];
		$config['app_home'] = $config['app_url'];
		// Lots of site_ config options were moved to app_. Eg site_title is now app_title
		foreach($config as $key => $value) {
			if(preg_match("/^app_/", $key)) {
				$site_key = str_replace('app_', "site_", $key);
				$config[$site_key] = $value;
			}
		}
		$config['mode'] = $config['env'];

		$GLOBALS['config'] = $config;
		$GLOBALS['rel'] = static::$config['app_relative_path'];
		$GLOBALS['abs'] = static::$config['app_absolute_path'];

		$GLOBALS['sql'] = static::$db;
	}

	public function bootstrap()
	{
		// <app>/common.php
		// Iframe root folder. Absolute.
		$iframe_folder = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;

		// App Folder. Absolute
		$full_self = $_SERVER['SCRIPT_FILENAME'];
		$app_folder = dirname($full_self);
		chdir($app_folder);

		if(empty(static::$config)) {
			$rel = $this->findRelation();
			static::$config = require($rel . "configuration.php"); // Load app spcific config file.

			if($rel == '') static::$config['app_folder'] = realpath('.');
			else static::$config['app_folder'] = realpath($rel);
			static::$config['app_relative_path'] = $rel;
		}
		static::$config['iframe_folder'] = $iframe_folder;

		//Find all path info
		static::$config['PHP_SELF'] = !empty($PHP_SELF) ? $PHP_SELF : $_SERVER["PHP_SELF"];

		//Absolute Path
		if(!isset(static::$config['app_absolute_path'])) {
			$path = dirname(static::$config['PHP_SELF']);
			//Go up until the correct path is found
			while (strlen($path) > 2) {
				if(file_exists($_SERVER["DOCUMENT_ROOT"] . $path . DIRECTORY_SEPARATOR . 'configuration.php')) break;
				else $path = dirname($path);
			}
			static::$config['app_absolute_path'] = str_replace('//','/', $path . DIRECTORY_SEPARATOR);
		}
		static::$config['current_page'] = str_replace(static::$config['app_absolute_path'], '/', static::$config['PHP_SELF']);

		// This is to tell us where the App is CLI, is hosted online or on our own system, etc.
		static::$config['server_host'] = 'cli';
		static::$config['server_online'] = true;
		if(isset($_SERVER['HTTP_HOST'])) {
			static::$config['server_host'] = $_SERVER['HTTP_HOST'];
			if(static::$config['server_host'] == 'localhost' 
				or static::$config['server_host'] == '127.0.0.1'
				or static::$config['server_host'] == $_SERVER['SERVER_ADDR']) {
					static::$config['server_online'] = false;
			}
		}
		if(static::$config['server_host'] == 'cli') static::$config['server_online'] = false;

		// Get the full URL of the website.
		if(!isset(static::$config['app_url']) and isset($_SERVER['HTTP_HOST'])) {
			static::$config['app_url']	= "http://" . $_SERVER['HTTP_HOST'] . static::$config['app_absolute_path'];
		}

		// Default Date and time formats.
		static::$config['date_format']	= '%D %b %Y';
		static::$config['time_format']	= '%D %b %Y, %h:%i %p';

		static::$config['date_format_php']	= phpDateFormat(static::$config['date_format']);
		static::$config['time_format_php']	= phpDateFormat(static::$config['time_format']);

		static::$config['iframe_url'] = '';
		if(!static::$config['server_online']) static::$config['iframe_url'] = "http://" . static::$config['server_host'] . '/iframe/';

		$this->initDevHelpers();
		$this->setupAutoIncludes();
		$this->registerGlobals();
		
		// Plugin System
		$i_plugin = false;
		if(file_exists(joinPath(static::$config['app_folder'],'plugins'))) {
			$i_plugin = new Plugin(joinPath(static::$config['app_folder'],'plugins'));

			$i_plugin->callHook('init');
		}
	}


	public function registerGlobals()
	{
		global $PARAM, $QUERY;
		// This is $_REQUERST without the problems asssociated with magic quotes
		$PARAM = unescapeQuery();
		$QUERY = escapeQuery($PARAM, true);
		if(!isset($QUERY['error']))	 {$QUERY['error'] = ''; $PARAM['error'] = '';}
		if(!isset($QUERY['success'])){$QUERY['success'] ='';$PARAM['success'] = '';}
	}

	private function initDevHelpers() 
	{
		/**
		 * The current envionment of the system. This will affect how errors will be shown
		 *  dev = Development 
		 *	test = Testing 
		 *	prod = Production 
		 */
		if(!isset(static::$config['env']) or isset($_GET['enable_debug_env'])) static::$config['env']	= 'dev'; //Default Config Mode // :UNSAFE:

		if(static::$config['env'] == 'dev') {
			// Error Handling
			error_reporting(E_ALL); 
			ini_set('display_errors', 1);
			
			$Logger = false;
			if(i($_REQUEST,'debug') == 'log') {
				$Logger = new Development\Logger;
				$Logger->log("\nRendering Request: $_SERVER[REQUEST_URI]");
			}
		}
		elseif(static::$config['env'] == 'prod') error_reporting(0);

		// Database connection is optional
		if(isset(static::$config['db_host']) and static::$config['db_host']) {
			static::$db = new DB\Sql(static::$config['db_host'], static::$config['db_user'], static::$config['db_password'], static::$config['db_database']); // Connect to DB
			DB\Sql::$mode = static::$config['env'];
		}
		if(!isset(static::$config['use_mvc']) or static::$config['use_mvc'] === false) static::$template = new iframe\Template;

		//Otherways it is a mess with google
		ini_set('url_rewriter.tags',"");
		ini_set('session.use_trans_sid',false); 
		if(isset($_SERVER["HTTP_HOST"])) session_start(); //Don't start the session for a console app.

		// Single user config option enabled. Login functions are not required.
		if(isset(static::$config['single_user']) and static::$config['single_user']) {
			$_SESSION['user_id'] = static::$config['single_user'];
		}
	}

	private function setupAutoIncludes()
	{
		// Register Models folder if it exist
		if(file_exists(joinPath(static::$config['app_folder'] , 'envls'))) set_include_path(get_include_path() . PATH_SEPARATOR .  joinPath(static::$config['app_folder'] , 'envls'));

		//Auto-include the application.php file
		if(isset(static::$config['app_relative_path']) and file_exists(static::$config['app_relative_path'] . 'includes/application.php')) {
			require(static::$config['app_relative_path'] . 'includes/application.php');
		}
	}

	/// Find the relation between the page we are in and the root folder.
	private function findRelation() {
		$rel = "";
		$depth = 0;
		while($depth < 10) { //We don't want an infinite loop - do we?
			if(file_exists($rel . "configuration.php")) break;
			else $rel .= "../";
			$depth++;
		}
		if($depth == 10) return false;
		
		return $rel; 
	}
}