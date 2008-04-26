<?php 
function render($file='') {
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
