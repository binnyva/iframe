<?php
namespace iframe;

class App {
	public static $config;
	public static $db;
	public static $template;

	function __construct()
	{
		$this->bootstrap();
	}

	public function render($variable_array = [], $options = [])
	{
		static::$template->render($variable_array, $options);
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
			static::$config = [];
			if(file_exists($rel . "configuration.php")) {
				static::$config = require($rel . "configuration.php"); // Load app spcific config file.
			}

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
		static::$config['app_asset_url'] = static::$config['app_url'];
		if(file_exists(static::$config['app_folder'] . '/assets')) {
			static::$config['app_asset_url'] = static::$config['app_url'] . '/assets';
		}

		// Default Date and time formats.
		static::$config['date_format']	= '%D %b %Y';
		static::$config['time_format']	= '%D %b %Y, %h:%i %p';

		static::$config['date_format_php']	= static::phpDateFormat(static::$config['date_format']);
		static::$config['time_format_php']	= static::phpDateFormat(static::$config['time_format']);

		static::$config['iframe_url'] = ''; // :TODO: :DEPRICATED:
		if(!static::$config['server_online']) static::$config['iframe_url'] = "http://" . static::$config['server_host'] . '/iframe/';

		static::$config['iframe_backward_compatible'] = true; // :TODO:

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
		$PARAM = $this->unescapeQuery();
		$QUERY = $this->escapeQuery($PARAM, true);
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

	/**
	 * This function will escape the user inputed data. It will check to see if magic quotes is on.
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

		if(!$ignore_magic_quote_setting and get_magic_quotes_gpc()) return $param_array;//If Magic quotes is enabled, just return the data - it is already escaped.

		foreach($param_array as $key => $value) {
			if(is_array($value)) { //Escape Arrays recursively
				$QUERY[$key] = escapeQuery($value,$ignore_magic_quote_setting); //:RECURSION:
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

		if(!$ignore_magic_quote_setting and !get_magic_quotes_gpc()) return $param_array;//If Magic quotes is disabled, just return the data - it is not escaped.

		while(list($key,$value) = each($param_array)) {
			if(is_array($value)) { //UnEscape Arrays recursively
				$PARAM[$key] = unescapeQuery($value,$ignore_magic_quote_setting); //:RECURSION:
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
	 *	$line - The line where the error occured [OPTIONAL]
	 *	$priority - The priority or the error - if its to high(>=10) the app will die. 10 has more priority than 1
	 */
	public function error($error_message, $error_title='Error', $file="", $line="", $priority=5) {
		static $error_call_count = 1;

		// This is to prevent recursion. Some of the functions used in the this fuction can return error.
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
					
					//Get the 5 lines surronding the error lines - before and after
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
											[ 	'template' => joinPath(__DIR__, '../classes/templates/error.php'),
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
	 *		it shows as a div message. Same goes for error message - it uses a different classname. Success uses the classname
	 *		'message-success' and Errors use the classname 'message-error'
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
	public function showMessage($message, $url='', $status="success",$extra_data=array(), $use_existing_params=true, $ajax = false) {
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
		
			if(strpos($url, 'http://') === false) {
				$url = joinPath($config['app_url'], $url);
			}
			
			$goto = str_replace('&amp;', '&', getLink($url, array($status=>$message) + $extra_data, $use_existing_params));
			header("Location:$goto");
		}
		exit;
	}
	/// Shortcut for showMessage when using ajax.
	public function showAjaxMessage($message, $type='success') {
		$this->showMessage($message,'',$type,array(),true,true);
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