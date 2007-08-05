<?php
$rel = findRelation();
include($rel . "configuration.php");

$this_folder = dirname(__FILE__) . '/';
include($this_folder . "includes/classes/Sql.php");
include($this_folder . "includes/config.php");
include($this_folder . "includes/functions.php");
include($this_folder . "includes/classes/MVC.php");

$PARAM = unescapeQuery();
$QUERY = escapeQuery($PARAM,true);
if(!isset($QUERY['error']))	 $QUERY['error'] = '';
if(!isset($QUERY['success']))$QUERY['success'] = '';

////////////////////////////////////// Functions /////////////////////////////////////////////////
//Find the relation between the page we are in and the root folder.
function findRelation() {
	$rel = "";
	$depth = 0;
	while($depth < 10) { //We don't want an infinite loop - do we?
		if(file_exists($rel . "configuration.php")) {
			break;
		} else {
			$rel .= "../";
		}
		$depth++;
	}
	return $rel; 
}

