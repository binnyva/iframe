<?php
$rel = findRelation();
if($rel !== false) include($rel . "configuration.php");

$iframe_folder = dirname(__FILE__) . '/';
$config['site_relative_path'] = $rel;
$config['iframe_folder'] = $iframe_folder;

include($iframe_folder . "includes/functions.php");
include($iframe_folder . "includes/config.php");

$PARAM = unescapeQuery();
$QUERY = escapeQuery($PARAM,true);
if(!isset($QUERY['error']))	 $QUERY['error'] = '';
if(!isset($QUERY['success']))$QUERY['success'] = '';

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
