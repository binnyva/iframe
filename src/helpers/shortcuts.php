<?php
/**
 * Formats the given text and return the result. For example, if 'avionics_filed_no12' is given,
 *		it will return 'Avionics Filed No 12'.
 * Argument: $value - The string that should be formated
 * Returns : The formated string
 */
function format($value) {
	$value = preg_replace(
		array(	"/[_\-]/",			//Changes 'hello_cruel-world' to 'hello cruel world'
				"/([a-zA-Z])(\d)/", //Changes 'no1' to 'no 1'
				"/([a-z])([A-Z])/"	//Changes 'helloWorld' to 'hello World'
		),
		array(" ","$1 $2","$1 $2"),
		$value);
	return ucwords($value);
}

/**
 * Removes all the formating from the given text and returns a string that could be used in an URL. 
 *		This fucntion lowercases the string and replaces all the special chars with '_'
 * Argument: $value - The string that should be un-formated
 * Returns : The unformated string
 */
function unformat($value) {
	$value = str_replace("'s", 's', $value); // Changes the text 'Binny's code' to 'Binnys code'. Because 'binny_s_code' looks bad.
	
	$value = preg_replace('/\W/','_',$value);	//Replace all special chars with an '_'
	$value = preg_replace('/__+/','_',$value);	//Replace multiple '_' with a single one.
	$value = preg_replace(
		array('/^_/','/_$/'), //Removes the '_' towards the beginning and the end of the string.
		array('_','_'),
		$value);
	return strtolower($value);
}

/**
 * The index function - Created this to avoid the extra isset() check. This will return false 
 *		if the specified index of the specified function is not set. If it there,
 *		this function will return that element.
 * Arguments:	$array - The array in which the item must be checked for
 *				$index - The index to be seached.
 *				$default_value - The value that must be returned if the item is not set
 * Example:
 *	if(i($_REQUEST, 'item')) {
 *		instead of 
 *	if(isset($_REQUEST['item']) and $_REQUEST['item']) {
 */
function i($array, $index=false, $default_value=false) {
	if($index === false) {
		if(isset($array)) return $array;
		return $default_value;
	}
	
	if(!isset($array[$index])) return $default_value;
	
	return $array[$index];
}

/**
 * Returns the given array after making it into a key format. If an element of the array has a key called 'id', that will be set as the key of that element.*
 * array('0'=>array('id'=>1,'name'=>'Binny'), '1'=>array('id'=>30,'name'=>'Bijoy')) will become...
 * array('1'=>array('id'=>1,'name'=>'Binny'), '30'=>array('id'=>30,'name'=>'Bijoy')) 
 */
function keyFormat($data, $primary_field='id') {
	$return = [];
	foreach($data as $row) {
		if(is_array($primary_field) and count($primary_field) == 2) {
			if(is_object($row) and isset($row->{$primary_field[0]})) $return[$row->{$primary_field[0]}] = $row->{$primary_field[1]};
			elseif(isset($row[$primary_field[0]])) $return[$row[$primary_field[0]]] = $row[$primary_field[1]];
		}
		else $return[$row[$primary_field]] = $row;
	}
	
	return $return;
}

/// Flattens a mutli colmn array - Removes everything but the first element of each element of the array.
function colFormat($data) {
	$return = array();
	foreach($data as $row) $return[] = current($row);
	
	return $return;
}

function idNameFormat($data, $fields = false) {
	if(!$fields) $fields = ['id', 'name'];
	return keyFormat($data, $fields);
}

/**
 * Transilation function
 * Arguments: $message - First argument is the string to be translated - this will be translated if a corresponding value is present in the locale file.
 *            The rest of the arguments will be passed to an sprintf() with the message - use this to put variables inside the sting.
 * Return: The translated string according to the current locale.
 * Example: t("Thanks for your input");
 *          t("Project '%s' has been deleted", 'Death Ray');
 */
function t() {
	global $locale, $locales;
	
	$arguments = func_get_args();
	$message = $arguments[0];
	$vars = array_splice($arguments, 1); //Gets all the arguments after the 1st argument.
	
	if(isset($locale) and isset($locales[$message])) {
		return call_user_func_array('sprintf', array_merge(array($locales[$message]), $vars));
    
    } else { // The necessary translation was not found.
		if (iframe\App::$config['env'] == 'dev' and $locale and $locale != 'en_EN') {
			if (isset($locale)) {
				error_log("l10n error: LANG: $locale, message: '$message'");
			} else {
				error_log("l10n error: LANG: no locale, message: '$message'");
			}
			return call_user_func_array('sprintf', array_merge(array($message . "[translate_me]"), $vars));
		}
		return call_user_func_array('sprintf', array_merge(array($message), $vars));
    }
}

/** 
 * Prints a array, an object or a scalar variable in an easy to view format.
 * Arguments  : $data - the variable that must be displayed
 * Link : http://www.bin-co.com/php/scripts/dump/
 */
function dump() {
	$args = func_get_args();
	$count = count($args) - 1;

	$backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

	print "<pre>";
	if($count) print "----------------------------------------------------------------------------------------------------\n";
	print "Called from : " . $backtrace[0]['file'] . ' at line ' . $backtrace[0]['line'] . "\n";
	foreach($args as $data) {
		if(is_array($data) or is_object($data)) { //If the given variable is an array, print using the print_r function.
			if(!$count) print "-----------------------\n";
			if(is_array($data)) print_r($data);
			else var_export($data);
			if(!$count) print "-----------------------\n";
			else print "=======================================================\n";
		} else {
			print "=========&gt;";
			print var_dump($data);
			print "&lt;=========\n";
		}
	}
	if($count) print "-------------------------------------------------------------------------------------------------------------------";
	print "</pre>\n";
}

function iapp($obj = false) {
	if($obj === 'db' or $obj == "sql") return iframe\App::$db;
	elseif($obj === 'template') return iframe\App::$template;
	elseif($obj === 'config') return iframe\App::$config;

	return false;
}
