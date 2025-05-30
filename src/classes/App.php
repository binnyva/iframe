<?php
namespace iframe;

class App {
	public static $config;
	public static $db;
	public static $template;
	public static $plugin;

	function __construct($options = [])
	{
		$this->bootstrap($options);
	}

	public function render($variable_array = [], $options = [])
	{
		static::$template->render($variable_array, $options);
	}

	public function bootstrap($options)
	{
		$path_separator = '/'; // Or DIRECTORY_SEPARATOR - but that was causing issues in windows.

		// Iframe src folder. Absolute.
		$iframe_folder = dirname(__DIR__) . $path_separator;

		// App Folder. Absolute
		$full_self = $_SERVER['SCRIPT_FILENAME'];
		$app_folder = dirname($full_self);
		chdir($app_folder);

		if(isset($options['config'])) {
			static::$config = $options['config'];
		} else {
			static::$config = ['app_name' => 'iFrame']; // Base template if no config file found.
		}

		$rel = $this->findRelation();
		if(file_exists($rel . "configuration.php")) {
			$app_config = require($rel . "configuration.php"); // Load app specific config file.
			static::$config = array_merge(static::$config, $app_config);
		}

		if($rel == '') static::$config['app_folder'] = realpath('.');
		else static::$config['app_folder'] = realpath($rel);
		static::$config['app_relative_path'] = $rel;

		static::$config['iframe_folder'] = $iframe_folder;

		//Find all path info
		static::$config['PHP_SELF'] = !empty($PHP_SELF) ? $PHP_SELF : $_SERVER["PHP_SELF"];

		//Absolute Path
		if(!isset(static::$config['app_absolute_path'])) {
			$path = dirname(static::$config['PHP_SELF']);
			//Go up until the correct path is found
			while (strlen($path) > 2) {
				if(file_exists($_SERVER["DOCUMENT_ROOT"] . $path . $path_separator . 'configuration.php')) break;
				else $path = dirname($path);
			}
			static::$config['app_absolute_path'] = str_replace('//','/', $path . $path_separator);
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

		if(!isset(static::$config['env'])) {
			static::$config['env'] = 'dev';
			if(static::$config['server_online']) static::$config['env'] = 'prod';
		}

		// Get the full URL of the website.
		if(!isset(static::$config['app_url']) and isset($_SERVER['HTTP_HOST'])) {
			static::$config['app_url']	= $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . static::$config['app_absolute_path'];
		}
		if(isset(static::$config['app_url'])) {
			static::$config['app_assets_url'] = static::$config['app_url'];
		}
		if(file_exists(static::$config['app_folder'] . '/assets')) {
			static::$config['app_assets_url'] = static::$config['app_url'] . 'assets';
		}

		// Default Date and time formats.
		static::$config['date_format']	= '%D %b %Y';
		static::$config['time_format']	= '%D %b %Y, %h:%i %p';

		static::$config['date_format_php']	= static::phpDateFormat(static::$config['date_format']);
		static::$config['time_format_php']	= static::phpDateFormat(static::$config['time_format']);

		static::$config['iframe_url'] = ''; // :TODO: :DEPRECATED:
		if(!static::$config['server_online'] and isset($_SERVER['REQUEST_SCHEME'])) {
			static::$config['iframe_url'] = $_SERVER['REQUEST_SCHEME'] . "://" . static::$config['server_host'] . '/iframe/';
		}

		static::$config['iframe_backward_compatible'] = true; // :TODO:

		// dump(static::$config); exit;

		$this->registerGlobals();
		$this->setupAutoIncludes();
		$this->initDevHelpers();
		$this->includeAppFiles();

		// Plugin System
		if(file_exists(joinPath(static::$config['app_folder'],'plugins'))) {
			static::$plugin = new iframe\Plugin(joinPath(static::$config['app_folder'],'plugins'));

			static::$plugin->callHook('init');
		} else {
			static::$plugin = new iframe\Plugin(false);
		}
	}

	public function registerGlobals()
	{
		global $PARAM, $QUERY;
		// This is $_REQUEST without the problems associated with magic quotes
		$PARAM = $this->unescapeQuery();
		$QUERY = $this->escapeQuery($PARAM, true);
		if(!isset($QUERY['error']))	 {$QUERY['error'] = ''; $PARAM['error'] = '';}
		if(!isset($QUERY['success'])){$QUERY['success'] ='';$PARAM['success'] = '';}
	}

	private function initDevHelpers() 
	{
		/**
		 * The current environment of the system. This will affect how errors will be shown
		 *  dev = Development 
		 *	dry = DryRun
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
			DB\Sql::$env = static::$config['env'];
			// dump("DB Connection Established");
		}
		if(!isset(static::$config['use_mvc']) or static::$config['use_mvc'] === false) static::$template = new iframe\Template;

		//Otherwise it is a mess with google
		ini_set('url_rewriter.tags',"");
		ini_set('session.use_trans_sid',false); 
		if(isset($_SERVER["HTTP_HOST"])) session_start(); //Don't start the session for a console app.

		// Single user config option enabled. Login functions are not required.
		if(isset(static::$config['single_user']) and static::$config['single_user']) {
			$_SESSION['user_id'] = static::$config['single_user'];
		}
	}

	// Auto include models/ files on 'new ModelName'.
	private function setupAutoIncludes()
	{
		spl_autoload_register(function ($class_name) {
			$class_file = joinPath(static::$config['app_folder'] , 'models', $class_name . ".php");
			if(file_exists($class_file)) {
				include_once $class_file;
			}
		});
	}

	private function includeAppFiles()
	{
		//Auto-include the includes/application.php file
		if(isset(static::$config['app_relative_path']) and file_exists(static::$config['app_relative_path'] . 'includes/application.php')) {
			require_once(static::$config['app_relative_path'] . 'includes/application.php');

			// All the variables defined in the application file goes into the global scope. :UGLY:
			$vars = get_defined_vars();
			foreach($vars as $name => $value) {
				$GLOBALS[$name] = $value;
			}
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

	/**
	 * This function will escape the user inputted data. It will check to see if magic quotes is on.
	 * If it is not on, it will manually escape(using mysql_real_escape_string()) all the requests and return it.
	 * Argument : $param_array - [OPTIONAL] The array that must be escaped. If empty, the function uses $_POST + $_GET
	 *			  $ignore_magic_quote_setting - [OPTIONAL] If set to true, this will escape the given array no matter what the get_magic_quotes_gpc() returns. Defaults to 'true'
	 * Return	: The escaped format of the array.
	 * Usage 	: $PARAM = unescapeQuery();
	 *			  $QUERY = escapeQuery($PARAM,true);
	 */
	private function escapeQuery($param_array = array(),$ignore_magic_quote_setting = false) {
		$QUERY = array();

		if(!$param_array)
			$param_array = $_POST + $_GET; //Don't use $_REQUEST - it has cookie/session info in it.
		
		if(function_exists('get_magic_quotes_gpc')) {
			if(!$ignore_magic_quote_setting and get_magic_quotes_gpc()) return $param_array;//If Magic quotes is enabled, just return the data - it is already escaped.
		}

		foreach($param_array as $key => $value) {
			if(is_array($value)) { //Escape Arrays recursively
				$QUERY[$key] = $this->escapeQuery($value,$ignore_magic_quote_setting); //:RECURSION:
			} else {
				if(isset($GLOBALS['sql'])) $value = $GLOBALS['sql']->escape($value); //If there is an SQL Connection,
				else $value = addslashes($value);
				
				//$QUERY[$key] = htmlspecialchars($value);
				$QUERY[$key] = $value;
			}
		}
		return $QUERY;
	}

	/**
	 * This function will undo the damage made by magic quotes. This will go thru the request array and unescape all the data.
	 * Argument : $param_array - [OPTIONAL] The array that must be unescaped. If empty, the function uses $_POST + $_GET
	 *			  $ignore_magic_quote_setting - [OPTIONAL] If set to true, this will escape the given array no matter what the get_magic_quotes_gpc() returns. Defaults to 'true'
	 * Return	: The proper format of the array - unescaped.
	 */
	private function unescapeQuery($param_array = array(),$ignore_magic_quote_setting = false) {
		$PARAM = array();

		if(!$param_array)
			$param_array = $_POST + $_GET; //Don't use $_REQUEST - it has cookie/session info in it.

		if(function_exists('get_magic_quotes_gpc')) {
			if(!$ignore_magic_quote_setting and !get_magic_quotes_gpc()) return $param_array;//If Magic quotes is disabled, just return the data - it is not escaped.
		}

		foreach($param_array as $key => $value) {
			if(is_array($value)) { //UnEscape Arrays recursively
				$PARAM[$key] = $this->unescapeQuery($value,$ignore_magic_quote_setting); //:RECURSION:
			} else {
				$PARAM[$key] = stripslashes($value);
			}
		}
		return $PARAM;
	}

	/**
	 * Prints out an error message if there is an error
	 * Arguments:
	 * 	$msg - The error message
	 *	$file - The file at which the error happened [OPTIONAL]
	 *	$line - The line where the error occurred [OPTIONAL]
	 *	$priority - The priority or the error - if its to high(>=10) the app will die. 10 has more priority than 1
	 */
	public static function error($error_message, $error_title='Error', $file="", $line="", $priority=5) {
		static $error_call_count = 1;

		// This is to prevent recursion. Some of the functions used in the this function can return error.
		$error_call_count++;
		if($error_call_count > 10) die("Too much recursion in the error() function call.");
		
		$config = static::$config;
		if($config['env'] == 'dev' or $config['env'] == 'test') {
			if($config['server_host'] == 'cli') {
				die($error_message);
			} else {
				if($file and $line) {
					$line = $line - 1;
					$error_message .= "In file '$file' at line $line..<br /><pre>";
					
					//Get the 5 lines surrounding the error lines - before and after
					$lines = explode("\n",file_get_contents($file));
					for($i=$line-5; $i<$line+5; $i++) {
						if($i == $line) $error_message .= '<span class="error-line">';
						$error_message .= "\n<span class='line-number'>$i)</span> ";
						$error_message .= str_replace(
							array('<',"\t"),
							array('&lt;','  '),
							$lines[$i]
						);//Trim it?
						if($i == $line) $error_message .= '</span>';
					}
					$error_message .= '</pre>';
				}

				if($config['iframe_url']) static::$template->addResource(joinPath($config['iframe_url'], 'css/iframe.css'), 'css', true);
				static::$template->render(['error_message' => $error_message, 'error_title' => $error_title], 
											[ 	'template' => joinPath(__DIR__, '../templates/error.php'),
												'use_layout'=>true,
												'use_exact_path'=> true]);
				exit;

			}
		} else {
			if($priority >= 10) die($msg);
		}
	}

	/**
	 * Shows the status of the system. If there is many success message, it will show up as a list. If there is just 1, 
	 *		it shows as a div message. Same goes for error message - it uses a different class name. Success uses the class name
	 *		'message-success' and Errors use the class name 'message-error'
	 */
	public function showStatus() {
		global $QUERY;
		if($QUERY['success']) {
			if(is_array($QUERY['success'])) {
				print "<ul class='message-success'>\n";
				foreach($QUERY['success'] as $msg) print "<li>$msg</li>\n";
				print "</ul>\n";
			} else {
				print "<div class='message-success'>$QUERY[success]</div>\n";
			}
		}
		
		if($QUERY['error']) {
			if(is_array($QUERY['error'])) {
				print "<ul class='message-error'>\n";
				foreach($QUERY['error'] as $msg) print "<li>$msg</li>\n";
				print "</ul>\n";
			} else {
				print "<div class='message-error'>$QUERY[error]</div>\n";
			}
		}
	}

	/**
	 * Shows the final message - redirects to a new page with the message in the URL
	 */
	public static function showMessage($message, $url='', $status="success",$extra_data=array(), $use_existing_params=true, $ajax = false) {
		$config = static::$config;
		if($config['server_host'] == 'cli') {
			print $message . "\n";
			if($status == 'error') exit;

		} elseif(isset($_REQUEST['ajax']) or $ajax) {
			//If it is an ajax request, Just print the data
			$success = '';
			$error = '';
			$insert_id = '';

			if($status == 'success') $success = $message;
			if($status == 'error' or $status == 'failure') $error = $message;

			$data = array(
				"success"	=> $success,
				"error"		=> $error
			) + $extra_data;

			print json_encode($data);

		} elseif(isset($_REQUEST['layout']) and $_REQUEST['layout']==='cli') {
			if($status === 'success') print $message . "\n";

		} else {
			if(!$url) {
				global $QUERY;
				$QUERY[$status] = $message;
				return;
			}
		
			if(strpos($url, $_SERVER['REQUEST_SCHEME'] . '://') === false) {
				$url = joinPath($config['app_url'], $url);
			}
			
			$goto = str_replace('&amp;', '&', getLink($url, array($status=>$message) + $extra_data, $use_existing_params));
			header("Location:$goto");
		}
		exit;
	}
	/// Shortcut for showMessage when using ajax.
	public static function showAjaxMessage($message, $type='success') {
		static::showMessage($message,'',$type,array(),true,true);
	}

	/**
	 * Converts the given MySQL Date format to PHP date formatting string. %Y-%m-%d becomes Y-m-d.
	 */
	private static function phpDateFormat($format_string) {
		$replace_rules = array(
			'%a' => 'D',
			'%b' => 'M',
			'%c' => 'n',
			'%D' => 'j\<\s\u\p\>S\<\/\s\u\p\>',
			'%e' => 'j',
			'%f' => 'u',
			'%j' => 'z',
			'%k' => 'G',
			'%l' => 'g',
			'%p' => 'A',
			'%r' => 'h:i:s A',
			'%S' => 's',
			'%T' => 'H:i:s',
			'%U' => 'W', // Limited functionality.
			'%u' => 'W',
			'%v' => 'W', // Limited functionality.
			'%V' => 'W', // Limited functionality.
			'%W' => 'l',
			'%'  => ''
		);

		return str_replace(array_keys($replace_rules), array_values($replace_rules), $format_string);
	}


}