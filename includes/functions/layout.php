<?php 
///////////////////////////// Layout Fuctions //////////////////////////////////////////
function printTop($title="") {
	printHead($title);
	printBegin();
}

function printHead($title="") {
	global $rel,$abs,$config;
	$title = ($title) ? $title : $config['site_title'];
	include($rel . "includes/layout/head.php");
}
function printBegin() {
	global $rel,$abs;
	include($rel . "includes/layout/begin.php");
	print "<!-- Begin Content -->";
}
function printEnd() {
	global $rel,$abs;
	print "<!-- End Content -->";
	include($rel . "includes/layout/end.php");
}
function render($file = '') {
	//If it is an ajax request, we don't have to render the page.
	if(isset($_REQUEST['ajax'])) {
		print '{"success":"Done","error":false}';
		return;
	}

	//Otherwise, render it.
	$GLOBALS['template']->render($file);
}

/**
 * This function makes sure that the appearance of the pager remains consistant accross the pages
 */
function showPager() {
	global $pager,$abs;
	$pager->showPager();
}
