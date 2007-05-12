<?php
/**
 * Create a link by joining the given URL and the parameters given as the second argument.
 * Arguments :  $url - The base url.
 *				$params - An array containing all the parameters and their values.
 * Return : The new url.
 * Example : 
 *			getLink("http://www.google.com/search",array("q"=>"binny","hello"=>"world","results"=>10));
 *					will return
 *			http://www.google.com/search?q=binny&hello=world&results=10
 */
function getLink($url,$params=array()) {
	if(!$params) return $url;
	$link = $url;
	if(strpos($link,'?') === false) $link .= '?'; //If there is no '?' add one at the end
	elseif(!preg_match('/(\?|\&(amp;)?)$/',$link)) $link .= '&amp;'; //If there is no '&' at the END, add one.
	
	$params_arr = array();
	foreach($params as $key=>$value) {
		if(gettype($value) == 'array') { //Handle array data properly
			foreach($value as $val) {
				$params_arr[] = $key . '[]=' . urlencode($val);
			}
		} else {
			$params_arr[] = $key . '=' . urlencode($value);
		}
	}
	$link .= implode('&amp;',$params_arr);
	
	return $link;
}

/**
 * Arguments :  $conditions - An array containing all the validaiton information.
 *				$show(Integer) - The value given here decides how the data should be returned - or printed.[OPTIONAL]
 *						1 = Prints the errors as an HTML List
 *						2 = Return the errors as a string(HTML list)
 *						3 = Return the errors as an array 
 *						4 = Return the errors as an array with field name as the key.
 *						Defaults to 1
 * Super powerful validaiton script for form fields. I may make this a class to do both serverside and 
 *			client side validaiton - both in the same package
 * :TODO: This function is not fully tested. It is not even partaily tested.
 * :TODO: Documentation needed desperatly
 
 The first argument - $conditions is an array with all the validaiton rule
 Each element of the array is one rule.
 Each rule is an associative array. The following keys are supported

 name	: The name of the field that should be checked. ($_REQUEST['date'] - here the name is 'date')
 is		: What sort data should MAKE AN ERROR. If the given type is found as the field value, an error will be raised. Example 'empty'
 title	: The human friendly name for the field (eg. 'Date of Birth')
 error  : The message that should be shown if there is a validation error
 value	: The programmer provided value. Some rules must have an additional value to be matched against. For example the '<' condition must have a value - the user inputed value and the value given in this index will be compared
 when	: This is a method to short-circut the validation. If this is false, or '0' validaiton will NOT take place. The rule will just be ignored.
 
 Example
 $conditions = array(
 	array(
 		'name'	=>	'username',
 		'is'	=>	'empty',
 		'error' =>	'Please provide a valid username'
 	),
 	array(
 		'name'	=>	'username',
 		'is'	=>	'length<',
 		'value'	=> 	3
 		'error' =>	'Make sure that then username has atleast 3 chars'
 	)
 )
 */
function check($conditions,$show=1) {
	$errors = array();
	$field_errors = array();
	foreach($conditions as $cond) {
		unset($title,$default_error,$error,$when,$input,$is,$value,$name);
		extract($cond);

		if(!isset($title))$title= format($name);
		if(!isset($name)) $name = unformat($title);
		$input = $_REQUEST[$name];
		
		$default_error = "Error in '$title' field!";
		if(!isset($error)) $error = $default_error;
		
		if(isset($when)) {
			if(($when === 0) or ($when === false)) {//Ok - don't validate this field - ignore erros if any
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
 * Converts a given PHP array to its eqvalent JSON String
 * Argument : $arr - The PHP array
 * Return : (String) - The JSON String.
 * Link : http://www.bin-co.com/php/scripts/array2json/
 */
function array2json($arr) {
	///:TODO: check for native json_encode function first.
    $parts = array();
    $is_list = false;

    //Find out if the given array is a numerical array
    $keys = array_keys($arr);
    $max_length = count($arr)-1;
    if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {//See if the first key is 0 and last key is length - 1
        $is_list = true;
        for($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
            if($i != $keys[$i]) { //A key fails at position check.
                $is_list = false; //It is an associative array.
                break;
            }
        }
    }

    foreach($arr as $key=>$value) {
        if(is_array($value)) { //Custom handling for arrays
            if($is_list) $parts[] = array2json($value); /* :RECURSION: */
            else $parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */
        } else {
            $str = '';
            if(!$is_list) $str = '"' . $key . '":';

            //Custom handling for multiple data types
            if(is_numeric($value)) $str .= $value; //Numbers
            elseif($value === false) $str .= 'false'; //The booleans
            elseif($value === true) $str .= 'true';
            else $str .= '"' . addslashes($value) . '"'; //All other things
            // :TODO: Is there any more datatype we should be in the lookout for? (Object?)

            $parts[] = $str;
        }
    }
    $json = implode(',',$parts);
    
    if($is_list) return '[' . $json . ']';//Return numerical JSON
    return '{' . $json . '}';//Return associative JSON
}

/**
 * A function for easily uploading files. This function will automatically generate a new 
 *        file name so that files are not overwritten.
 * Arguments:     $file_id - The name of the input field contianing the file.
 *                $folder  - The folder to which the file should be uploaded to - it must be writable. OPTIONAL
 *                $types   - A list of comma(,) seperated extensions that can be uploaded. If it is empty, anything goes OPTIONAL
 * Returns  : This is somewhat complicated - this function returns an array with two values...
 *                The first element is randomly generated filename to which the file was uploaded to.
 *                The second element is the status - if the upload failed, it will be 'Error : Cannot upload the file 'name.txt'.' or something like that
 */
function upload($file_id, $folder="", $types="") {
    if(!$_FILES[$file_id]['name']) return array('','No file specified');

    $file_title = $_FILES[$file_id]['name'];
    //Get file extension
    $ext_arr = split("\.",basename($file_title));
    $ext = strtolower($ext_arr[count($ext_arr)-1]); //Get the last extension

    //Not really uniqe - but for all practical reasons, it is
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
    $uploadfile = $folder . $file_name;

    $result = '';
    //Move the file from the stored location to the new location
    if (!move_uploaded_file($_FILES[$file_id]['tmp_name'], $uploadfile)) {
        $result = "Cannot upload the file '".$_FILES[$file_id]['name']."'"; //Show error if any.
        if(!file_exists($folder)) {
            $result .= " : Folder don't exist.";
        } elseif(!is_writable($folder)) {
            $result .= " : Folder not writable.";
        } elseif(!is_writable($uploadfile)) {
            $result .= " : File not writable.";
        }
        $file_name = '';
        
    } else {
        if(!$_FILES[$file_id]['size']) { //Check if the file is made
            @unlink($uploadfile);//Delete the Empty file
            $file_name = '';
            $result = "Empty file found - please use a valid file."; //Show the error message
        } else {
            chmod($uploadfile,0777);//Make it universally writable.
        }
    }

    return array($file_name,$result);
}

/**
 * Function  : sendEMail()
 * Agruments : $from - don't make me explain these
 *			  $to
 *			  $message
 *			  $subject 
 * Sends an email with the minimum amount of fuss.
 */
function sendEMail($from_email,$to,$message,$subject) {
	global $Config;
	
	$from_name = $Config['site_title'];
	$site = $Config['site_url'];
	$from_email = $Config['site_email'];
	
	/*Clean The mail of BCC Header Injections before sending the mail*/
	//Code taken from http://in.php.net/manual/en/ref.mail.php#59012

	// Attempt to defend against header injections: 
	$badStrings = array("Content-Type:", 
						"MIME-Version:", 
						"Content-Transfer-Encoding:", 
						"bcc:", 
						"cc:"); 
	
	// Loop through each POST'ed value and test if it contains 
	// one of the $badStrings: 
	foreach($_POST as $k => $v){ 
		foreach($badStrings as $v2){ 
			if(strpos($v, $v2) !== false){ 
				header("HTTP/1.0 403 Forbidden"); 
				exit; 
			} 
		} 
	}     
	/*******************************************************************************/
	$from_str = "$from_name <$from_email>";
	
	if(strpos($message,"<br")===false) { //A Plain Text message
		$type = "text/plain";
	} else { //HTML message
		$type = "text/html";
	}

	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: $type; charset=iso-8859-1\r\n";
	$headers .= "From: $from_str";
	
	if(mail($to,$subject,$message,$headers)) return true;
	else return false;
}
 
