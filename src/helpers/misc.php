<?php
/**
 * Takes one or more file names and combines them, using the correct path separator for the 
 * 		current platform and then return the result.
 * Arguments: The parts that make the final path.
 * Example: joinPath('/var','www/html/','try.php'); // returns '/var/www/html/try.php'
 */
function joinPath() {
	$path = '';
	$arguments = func_get_args();
	$args = array();
	foreach($arguments as $a) if($a) $args[] = $a;//Removes the empty elements
	
	$arg_count = count($args);
	for($i=0; $i<$arg_count; $i++) {
		$folder = $args[$i];
		
		if($i != 0 and $folder[0] == DIRECTORY_SEPARATOR) $folder = substr($folder,1); //Remove the first char if it is a '/' - and its not in the first argument
		if($i != $arg_count-1 and substr($folder,-1) == DIRECTORY_SEPARATOR) $folder = substr($folder,0,-1); //Remove the last char - if its not in the last argument
		
		$path .= $folder;
		if($i != $arg_count-1) $path .= DIRECTORY_SEPARATOR; //Add the '/' if its not the last element.
	}
	return $path;
}

/**
 * Create a link by joining the given URL and the parameters given as the second argument.
 * Arguments :  $url - The base url.
 *				$params - An array containing all the parameters and their values.
 *				$use_existing_arguments - Use the parameters that are present in the current page
 * Return : The new url.
 * Example : 
 *			getLink("http://www.google.com/search",array("q"=>"binny","hello"=>"world","results"=>10));
 *					will return
 *			http://www.google.com/search?q=binny&hello=world&results=10
 */
function getLink($url,$params=array(),$use_existing_arguments=false) {
	if(!$params and !$use_existing_arguments) return $url;
	if($use_existing_arguments) $params = $params + $_GET;
	
	$link = $url;
	
	if(strpos($link,'?') === false) {
		$existing_parameters = array();
	} else { // This will make sure that even if the specified param exists in the given url, it will be over written.
		$url_parts = explode('?', $url);
		$link = $url_parts[0];
		$existing_parameters = array();
		
		if($url_parts[1]) {
			$all_url_parameters = preg_split("/\&(amp\;)?/", $url_parts[1]);
			foreach($all_url_parameters as $part) {
				list($name, $value) = explode("=", $part);
				$existing_parameters[$name] = $value;
			}
		}
	}
	if($existing_parameters) $params = $params + $existing_parameters;
	
	$params_arr = array();
	foreach($params as $key=>$value) {
		if($value === null) continue; // If the value is given as null, don't show it in the query at all. Use arg=>"null" if you want a string null in the query.
		if($use_existing_arguments) {// Success or Error message don't have to be shown.
			if(($key == 'success' and isset($_GET['success']) and $_GET['success'] == $value)
			 	or ($key == 'error' and isset($_GET['error']) and $_GET['error'] == $value)) continue;
		}
		
		if(gettype($value) == 'array') { //Handle array data properly
			foreach($value as $val) {
				$params_arr[] = $key . '[]=' . urlencode($val);
			}
		} else {
			$params_arr[] = $key . '=' . urlencode($value);
		}
	}
	if($params_arr) $link = $link . '?' . implode('&amp;',$params_arr);
	
	return $link;
}

/**
 * Arguments :  $conditions - An array containing all the validation information.
 *				$show(Integer) - The value given here decides how the data should be returned - or printed.[OPTIONAL]
 *						1 = Prints the errors as an HTML List
 *						2 = Return the errors as a string(HTML list)
 *						3 = Return the errors as an array 
 *						4 = Return the errors as an array with field name as the key.
 *						Defaults to 1
 * Super powerful validation script for form fields. I may make this a class to do both server side and 
 *			client side validation - both in the same package
 * :TODO: This function is not fully tested. It is not even partially tested.
 * :TODO: Documentation needed desperately
 * :TODO: Change this function to a class.
 * The first argument - $conditions is an array with all the validation rule
 * Each element of the array is one rule.
 * Each rule is an associative array. The following keys are supported
 * 
 * name	: The name of the field that should be checked. ($_REQUEST['date'] - here the name is 'date')
 * is		: What sort data should MAKE AN ERROR. If the given type is found as the field value, an error will be raised. Example 'empty'
 * title	: The human friendly name for the field (eg. 'Date of Birth')
 * error  : The message that should be shown if there is a validation error
 * value	: The programmer provided value. Some rules must have an additional value to be matched against. For example the '<' condition must have a value - the user imputed value and the value given in this index will be compared
 * when	: This is a method to short-circuit the validation. If this is false, or '0' validation will NOT take place. The rule will just be ignored.
 *  
 * Example :
 * $conditions = array(
 *	 array(
 *		 'name'	=>	'username',
 *		 'is'	=>	'empty',
 *		 'error' =>	'Please provide a valid username'
 *	 ),
 *	 array(
 *		 'name'	=>	'username',
 *		 'is'	=>	'length<',
 *		 'value'	=> 	3,
 *		 'error' =>	'Make sure that then username has at least 3 chars'
 *	 )
 * )
 */
function check($conditions,$show=1) {
	$errors = array();
	$field_errors = array();
	foreach($conditions as $cond) {
		unset($title,$default_error,$error,$when,$input,$is,$value,$name,$value_field);
		extract($cond);

		if(!isset($title))$title= format($name);
		if(!isset($name)) $name = unformat($title);
		$input = '';
		if(!empty($_REQUEST[$name])) $input = $_REQUEST[$name];
		if(isset($value_field)) {
			$value = $_REQUEST[$value_field];
		}
		
		$default_error = "Error in '$title' field!";
		if(!isset($error)) $error = $default_error;
		
		if(isset($when)) {
			if(($when === 0) or ($when === false)) {//Ok - don't validate this field - ignore errors if any
				continue;
			} else if ($when != "") { //When error
				$errors[] = $error;
			}
		}

		switch($is) {
			case 'empty':
				if(!$input) {
					if($error == $default_error) $error = "The $title is not provided";
					$field_errors[$name][] = $error;
				}
			break;
			case 'not':
				if($error == $default_error) $error = "The $title should be '$value'";
				if($input != $value) $field_errors[$name][] = $error;
			break;
			case 'equal':
				if($error == $default_error) $error = "The $title should field must not be '$value'";
				if($input == $value) $field_errors[$name][] = $error;
			break;
			
			//Numeric Checks			
			case '>':
			case 'greater':
				if($input > $value) $field_errors[$name][] = $error;
			break;
			case '<':
			case 'lesser':
				if($input < $value) $field_errors[$name][] = $error;
			break;
			
			//Length Checks
			case 'length<':
				if(strlen($input) < $value) $field_errors[$name][] = $error;
			break;
			case 'length>':
				if(strlen($input) > $value) $field_errors[$name][] = $error . $value . ' : ' . strlen($input);
			break;

			case 'nan':
			case 'not_number': //Warning: Decimals will get through
				if($input and !is_numeric($input)) {
					$field_errors[$name][] = "The " . $title . " should be a number";
			}
			break;
			
			case 'not_email': //If the field does not match the email regexp, an error is shown
				if(!preg_match('/^[\w\-\.]+\@[\w\-\.]+\.[a-z\.]{2,5}$/',$input)) {
					if($title) $error = "The " . $title . " should be a valid email address";
					else $error = "Invalid Email address provided";
					$field_errors[$name][] = $error;
				}
				break;
			case 'has_weird': //Check for weird chars
				if(!preg_match('/^[\w\-]*$/',$input)) {
					if($title) $error = "The " . $title ." should not have weird characters";
					else $error = "Weird characters where found in the input";
					$field_errors[$name][] = $error;
				}
				break;
			case 'not_name': //Check for chars that cannot appear in a title
				if(!preg_match("/^[\w\'\(\)\,\.\/ ]*$/",$input)) {
					if($title) $error = "The " . $title ." has invalid characters";
					else $error = "Invalid characters where found in the input";
					$field_errors[$name][] = $error;
				}
				break;

			//RegExp
			case 'dont_match':
			case 'not_match':
			case '!match':
				if(!preg_match("/$value/",$input)) $field_errors[$name][] = $error;
			break;
			case 'match':
				if(preg_match("/$value/",$input)) $field_errors[$name][] = $error;
			break;
		}
	}
	
	//Put all errors into one array
	if($field_errors) {
		foreach($field_errors as $name=>$arr) {
			$errors = array_merge($errors,$arr);
		}
		$errors = array_values(array_diff($errors,array('')));
	}
	
	if(!$errors) return '';

	$error_message = "<ul class='validation-errors'>\n<li>";
	$error_message .= implode( "</li>\n<li>",$errors );
	$error_message .= "</li>\n</ul>";
	
	if($show == 1) {//Just show the errors as one list if the user wants it so
		print $error_message;

	} else if($show == 2) { //Return the errors as a string(HTML list)
		return $error_message;

	} else if($show == 3) {//Return the errors as an array
		return $errors;
	
	} else { //Return the errors as a array with field information
		return $field_errors;
	}
}

/**
 * A function for easily uploading files. This function will automatically generate a new 
 *		file name so that files are not overwritten.
 * Arguments:	 $file_id - The name of the input field containing the file.
 *				$folder  - The folder to which the file should be uploaded to - it must be writable. OPTIONAL
 *				$types   - A list of comma(,) separated extensions that can be uploaded. If it is empty, anything goes OPTIONAL
 * Returns  : This is somewhat complicated - this function returns an array with two values...
 *				The first element is randomly generated filename to which the file was uploaded to.
 *				The second element is the status - if the upload failed, it will be 'Error : Cannot upload the file 'name.txt'.' or something like that
 */
function upload($file_id, $folder="", $types="") {
	if(!$_FILES[$file_id]['name']) return array('','No file specified');

	$file_title = $_FILES[$file_id]['name'];
	//Get file extension
	$ext_arr = explode(".",basename($file_title));
	$ext = strtolower($ext_arr[count($ext_arr)-1]); //Get the last extension

	//Not really unique - but for all practical reasons, it is
	$uniqer = substr(md5(uniqid(rand(),1)),0,5);
	$file_name = $uniqer . '_' . $file_title;//Get Unique Name

	$all_types = explode(",",strtolower($types));
	if($types) {
		if(in_array($ext,$all_types));
		else {
			$result = "'".$_FILES[$file_id]['name']."' is not a valid file."; //Show error if any.
			return array('',$result);
		}
	}

	//Where the file must be uploaded to
	if($folder) $folder .= '/';//Add a '/' at the end of the folder
	$upload_file = $folder . $file_name;

	$result = '';
	//Move the file from the stored location to the new location
	if (!move_uploaded_file($_FILES[$file_id]['tmp_name'], $upload_file)) {
		$result = "Cannot upload the file '".$_FILES[$file_id]['name']."'"; //Show error if any.
		if(!file_exists($folder)) {
			$result .= " : Folder don't exist.";
		} elseif(!is_writable($folder)) {
			$result .= " : Folder not writable.";
		} elseif(!is_writable($upload_file)) {
			$result .= " : File not writable.";
		}
		$file_name = '';
		
	} else {
		if(!$_FILES[$file_id]['size']) { //Check if the file is made
			@unlink($upload_file);//Delete the Empty file
			$file_name = '';
			$result = "Empty file found - please use a valid file."; //Show the error message
		} else {
			chmod($upload_file,0777);//Make it universally writable.
		}
	}

	return array($file_name,$result);
}
 
/**
 * Link: http://www.bin-co.com/php/scripts/load/
 * Version : 2.00.A
 */
function load($url,$options=array()) {
	$default_options = array(
		'method'		=> 'get',
		'post_data'		=> array(),		// The data that must be send to the URL as post data.
		'return_info'	=> false,		// If true, returns the headers, body and some more info about the fetch.
		'return_body'	=> true,		// If false the function don't download the body - useful if you just need the header or last modified instance.
		'cache'			=> false,		// If true, saves a copy to a local file - so that the file don't have multiple times.
		'cache_folder'	=> '/tmp/php-load-function/', // The folder to where the cache copy of the file should be saved to.
		'cache_timeout'	=> 0,			// If the cached file is older that given time in minutes, it will download the file again and cache it.
		'referer'		=> '',			// The referer of the url.
		'headers'		=> array(),		// Custom headers
		'session'		=> false,		// If this is true, the following load() calls will use the same session - until load() is called with session_close=true.
		'session_close'	=> false,
		'username'		=> false,
		'password'		=> false
	);
	// Sets the default options.
	foreach($default_options as $opt=>$value) {
		if(!isset($options[$opt])) $options[$opt] = $value;
	}

	$url_parts = parse_url($url);
	$ch = false;
	$info = array(//Currently only supported by curl.
		'http_code'	=> 200
	);
	$response = '';
	
	
	$send_header = array(
		'User-Agent' => 'BinGet/1.00.A (http://www.bin-co.com/php/scripts/load/)'
	) + $options['headers']; // Add custom headers provided by the user.
	
	if($options['cache']) {
		$cache_folder = $options['cache_folder'];
		if(!file_exists($cache_folder)) {
			$old_umask = umask(0); // Or the folder will not get write permission for everybody.
			mkdir($cache_folder, 0777);
			umask($old_umask);
		}
		
		$cache_file_name = md5($url) . '.cache';
		$cache_file = joinPath($cache_folder, $cache_file_name); //Don't change the variable name - used at the end of the function.
		
		if(file_exists($cache_file) and filesize($cache_file) != 0) { // Cached file exists - return that.
			$timed_out = false;
			if($options['cache_timeout']) {
				if(((time() - filemtime($cache_file)) / 60) > $options['cache_timeout']) $timed_out = true;  // If the cached file is older than the timeout value, download the URL once again.
			}
			
			if(!$timed_out) {
				$response = file_get_contents($cache_file);
				
				//Separate header and content
				$separator_character_count = 4;
				$separator_position = strpos($response,"\r\n\r\n");
				if(!$separator_position) {
					$separator_position = strpos($response,"\n\n");
					$separator_character_count = 2;
				}
				// If the real separator(\r\n\r\n) is NOT found, search for the first < char.
				if(!$separator_position) {
					$separator_position = strpos($response,"<"); //:HACK:
					$separator_character_count = 0;
				}
				
				$body = '';
				$header_text = '';
				if($separator_position) {
					$header_text = substr($response,0,$separator_position);
					$body = substr($response,$separator_position+$separator_character_count);
				}
				
				foreach(explode("\n",$header_text) as $line) {
					$parts = explode(": ",$line);
					if(count($parts) == 2) $headers[$parts[0]] = chop($parts[1]);
				}
				$headers['cached'] = true;
				
				if(!$options['return_info']) return $body;
				else return array('headers' => $headers, 'body' => $body, 'info' => array('cached'=>true));
			}
		}
	}

	if(!function_exists("curl_init")) {
		return false;
	}
		
	if(isset($options['post_data']) and $options['post_data']) { //There is an option to specify some data to be posted.
		$page = $url;
		$options['method'] = 'post';
		
		if(is_array($options['post_data'])) { //The data is in array format.
			$post_data = array();
			foreach($options['post_data'] as $key => $value) {
				$post_data[] = "$key=" . urlencode($value);
			}
			$url_parts['query'] = implode('&', $post_data);
		
		} else { //Its a string
			$url_parts['query'] = $options['post_data'];
		}
	} else {
		if(isset($options['method']) and $options['method'] == 'post') {
			$page = $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'];
		} else {
			$page = $url;
		}
	}

	if($options['session'] and isset($GLOBALS['_binget_curl_session'])) $ch = $GLOBALS['_binget_curl_session']; //Session is stored in a global variable
	else $ch = curl_init($url_parts['host']);
	
	curl_setopt($ch, CURLOPT_URL, $page) or die("Invalid cURL Handle Resource");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Just return the data - not print the whole thing.
	curl_setopt($ch, CURLOPT_HEADER, true); //We need the headers
	curl_setopt($ch, CURLOPT_NOBODY, !($options['return_body'])); //The content - if true, will not download the contents. There is a ! operation - don't remove it.
	if(isset($options['encoding'])) curl_setopt($ch, CURLOPT_ENCODING, $options['encoding']); // Used if the encoding is gzip.
	if(isset($options['method']) and $options['method'] == 'post' and isset($url_parts['query'])) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $url_parts['query']);
	}
	//Set the headers our spiders sends
	curl_setopt($ch, CURLOPT_USERAGENT, $send_header['User-Agent']); //The Name of the UserAgent we will be using ;)
	unset($send_header['User-Agent']);
	
	$custom_headers = array();
	foreach($send_header as $key => $value) $custom_headers[] = "$key: $value";
	if(isset($options['modified_since']))
		$custom_headers[] = "If-Modified-Since: ".gmdate('D, d M Y H:i:s \G\M\T',strtotime($options['modified_since']));
	curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);
	if($options['referer']) curl_setopt($ch, CURLOPT_REFERER, $options['referer']);

	curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/binget-cookie.txt"); //If ever needed...
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	if($options['username'] and $options['password']) {
		curl_setopt($ch, CURLOPT_USERPWD, $options['username'].':'.$options['password']);
	}
   
	if($custom_headers) curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);
	$response = curl_exec($ch);
	$info = curl_getinfo($ch); //Some information on the fetch
	
	if($options['session'] and !$options['session_close']) $GLOBALS['_binget_curl_session'] = $ch; //Don't close the curl session. We may need it later - save it to a global variable
	else curl_close($ch);  //If the session option is not set, close the session.

	//Get the headers in an associative array
	$headers = array();

	if($info['http_code'] == 404) {
		$body = "";
		$headers['Status'] = 404;
	} else {
		// Separate header and content
		$header_text = '';
		$body = $response;
		if(isset($info['header_size'])) {
		  $header_text = substr($response, 0, $info['header_size']);
		  $body = substr($response, $info['header_size']);
		} else {
			$header_text = reset(explode("\r\n\r\n", trim($response)));
			$body = str_replace($header_text."\r\n\r\n", '', $response);
		}		
		
		// If there is a redirect, there will be multiple headers in the response. We need just the last one.
		$header_parts = explode("\r\n\r\n", trim($header_text));
		$header_text = end($header_parts);
		
		foreach(explode("\n",$header_text) as $line) {
			$parts = explode(": ",$line);
			if(count($parts) == 2) $headers[$parts[0]] = chop($parts[1]);
		}
		
		// :BUGFIX: :UGLY: Some URLs(IMDB has this issue) will do a redirect without the new Location in the header. It will be in the url part of info. If we get such a case, set the header['Location'] as info['url']
		if(!isset($header['Location']) and isset($info['url'])) {
			$header['Location'] = $info['url'];
			$header_text .= "\r\nLocation: $header[Location]";
		}
		
		$response = $header_text . "\r\n\r\n" . $body;
	}
	
	if(isset($cache_file)) { //Should we cache the URL?
		file_put_contents($cache_file, $response);
	}

	if($options['return_info']) return array('headers' => $headers, 'body' => $body, 'info' => $info, 'curl_handle'=>$ch);
	return $body;
} 


/**
 * This function will take a pattern and a folder as the argument and go thru it(recursively if needed)and return the list of 
 *			   all files in that folder.
 * Link			 : http://www.bin-co.com/php/scripts/filesystem/ls/
 * Arguments	 :  $pattern - The pattern to look out for [OPTIONAL]
 *					$folder - The path of the directory of which's directory list you want [OPTIONAL]
 *					$recursively - The function will traverse the folder tree recursively if this is true. Defaults to false. [OPTIONAL]
 *					$options - An array of values 'return_files' or 'return_folders' or both
 * Returns	   : A flat list with the path of all the files(no folders) that matches the condition given.
 */
function ls($pattern="*", $folder="", $recursively=false, $options=array('return_files','return_folders')) {
	if($folder) {
		$current_folder = realpath('.');
		if(in_array('quiet', $options)) { // If quiet is on, we will suppress the 'no such folder' error
			if(!file_exists($folder)) return array();
		}
		
		if(!chdir($folder)) return array();
	}
	
	$get_files	= in_array('return_files', $options);
	$get_folders= in_array('return_folders', $options);
	$both = array();
	$folders = array();
	
	// Get the all files and folders in the given directory.
	if($get_files) $both = glob($pattern, GLOB_BRACE + GLOB_MARK);
	if($recursively or $get_folders) $folders = glob("*", GLOB_ONLYDIR + GLOB_MARK);
	
	//If a pattern is specified, make sure even the folders match that pattern.
	$matching_folders = array();
	if($pattern !== '*') $matching_folders = glob($pattern, GLOB_ONLYDIR + GLOB_MARK);
	
	//Get just the files by removing the folders from the list of all files.
	$all = array_values(array_diff($both,$folders));
		
	if($recursively or $get_folders) {
		foreach ($folders as $this_folder) {
			if($get_folders) {
				//If a pattern is specified, make sure even the folders match that pattern.
				if($pattern !== '*') {
					if(in_array($this_folder, $matching_folders)) array_push($all, $this_folder);
				}
				else array_push($all, $this_folder);
			}
			
			if($recursively) {
				// Continue calling this function for all the folders
				$deep_items = ls($pattern, $this_folder, $recursively, $options); # :RECURSION:
				foreach ($deep_items as $item) {
					array_push($all, $this_folder . $item);
				}
			}
		}
	}
	
	if($folder) chdir($current_folder);
	return $all;
}

/// Parses the given HTML string using the domDocument class and returns a dom node.
function parseHTML($html) {
	$dom = new domDocument;
	@$dom->loadHTML($html);
	$dom->preserveWhiteSpace = false;
	return $dom;
}

/**
 * Get DOM elements based on the given CSS Selector - V 1.00.A Beta
 * Direct port of http://www->openjs->com/scripts/dom/css_selector/
 */
function getElementsBySelector($all_selectors, $document) {
	$selected = array();
	
	$all_selectors = preg_replace(array('/^\s*([^\w])\s*$/', '/\s{2,}/'),array("$1", ' '), $all_selectors);//Remove the 'beutification' spaces
	$selectors = explode(",", $all_selectors);
	
	// COMMA:
	$comma_count = 0;
	foreach ($selectors as $selector) {
		$comma_count++;
		$context = array($document);
		$inheritors = explode(" ", $selector); 

		// SPACE:
		$space_count = 0;
		foreach($inheritors as $element) {
			$space_count++;
			//This part is to make sure that it is not part of a CSS3 Selector
			$left_bracket = strpos($element, "[");
			$right_bracket= strpos($element, "]");
			$pos = strpos($element, "#"); //ID
			if($pos !== false and !($pos > $left_bracket and $pos < $right_bracket)) {
				$parts = explode("#", $element);
				$tag = $parts[0];
				$id = $parts[1];
				$ele = false;
				
				//$ele = $document->getElementById($id); // Does'nt work - PHP bug, I guess.
				$all = getElementsBySelectorGetElements($context, $tag);
				foreach($all as $eles) {
					if($eles->getAttribute("id") == $id) {
						$ele = $eles;
						break;
					}
				}
				
				if(!$ele or ($tag and strtolower($ele->nodeName) != $tag)) { //Specified element not found
					continue 2;
				}
				
				//If Id is the last element, return it as a single element and not as an array.
				if(count($inheritors) == $space_count and count($selectors) == $comma_count) return $ele;

				$context = array($ele);
				continue;
			}

			$pos = strpos($element, ".");//Class
			if($pos !== false and !($pos > $left_bracket and $pos < $right_bracket)) {
				$parts = explode('.', $element);
				$tag = $parts[0];
				$class_name = $parts[1];

				$found = getElementsBySelectorGetElements($context, $tag);
				$context = array();
				
 				foreach($found as $fnd) {
 					if(preg_match('/(^|\s)'.$class_name.'(\s|$)/', $fnd->getAttribute("class"))) $context[] = $fnd;
 				}
				continue;
			}

			if(strpos($element, '[') !== false) {//If the char '[' appears, that means it needs CSS 3 parsing
				// Code to deal with attribute selectors
				$tag = '';
				if (preg_match('/^(\w*)\[(\w+)([=~\|\^\$\*]?)=?[\'"]?([^\]\'"]*)[\'"]?\]$/', $element, $matches)) {
					$tag = $matches[1];
					$attr = $matches[2];
					$operator = $matches[3];
					$value = $matches[4];
				}
				$found = getElementsBySelectorGetElements($context, $tag);
				$context = array();
				foreach ($found as $fnd) {
 					if($operator == '=' and $fnd->getAttribute($attr) != $value) continue;
					if($operator == '~' and !preg_match('/(^|\\s)'.$value.'(\\s|$)/', $fnd->getAttribute($attr))) continue;
					if($operator == '|' and !preg_match('/^'.$value.'-?/', $fnd->getAttribute($attr))) continue;
					if($operator == '^' and strpos($value, $fnd->getAttribute($attr)) === false) continue;
					if($operator == '$' and strrpos($value, $fnd->getAttribute($attr)) != (strlen($fnd->getAttribute($attr)) - strlen($value))) continue;
					if($operator == '*' and strpos($value, $fnd->getAttribute($attr)) !== false) continue;
					else if(!$fnd->getAttribute($attr)) continue;
					
					$context[] = $fnd;
 				}

				continue;
			}

			//Tag selectors - no class or id specified->
			$found = getElementsBySelectorGetElements($context,$element);
			$context = $found;
		}
		foreach($context as $con) $selected[] = $con;
	}
	return $selected;
}

// Grab all of the tagName elements within current context	
// Helper function for getElementsBySelector()
function getElementsBySelectorGetElements($context, $tag='*') {
	if(empty($tag)) $tag = '*';
	// Get elements matching tag, filter them for class selector
	$found = array();
	foreach ($context as $con) {
		$eles = $con->getElementsByTagName($tag);
		foreach($eles as $ele) $found[] = $ele;
	}
	return $found;
}
