<?php
if(!function_exists("findRelation")) {
//Find the relation between the page we are in and the root folder.
function findRelation() {
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

$rel = findRelation();

$iframe_folder = dirname(__FILE__) . DIRECTORY_SEPARATOR;
if($rel !== false) {
	require($rel . "configuration.php");
	$config['site_relative_path'] = $rel;
} else {
	require($iframe_folder . 'configuration.php');
	$config['site_relative_path'] = '';
}
$config['iframe_folder'] = $iframe_folder;

require($config['iframe_folder'] . "includes/functions.php");

$PARAM = unescapeQuery();
$QUERY = escapeQuery($PARAM,true);
if(!isset($QUERY['error']))	 $QUERY['error'] = '';
if(!isset($QUERY['success']))$QUERY['success'] = '';

require($config['iframe_folder'] . "includes/config.php");
