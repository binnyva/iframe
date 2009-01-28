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
	$pager->printPager();
}

//////////////////////// Layout Functions - DEPRECATED ///////////////////
function showHead($title='') {
	global $template, $config;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<title><?=$title?></title>
<link href="<?=joinPath($config['site_url'],'css/style.css')?>" rel="stylesheet" type="text/css" />
<?=implode("\n", $template->css_includes);?>
<?php
}


function showBegin() {
	global $config;
?>
</head>
<body>
<?php if(isset($config['site_title'])) { ?>
<div id="header">
<h1 id="logo"><a href="<?=$config['site_url']?>"><?=$config['site_title']?></a></h1>
</div>
<?php } ?>

<div id="content">
<!-- Begin Content -->
<?php
}

function showTop($title='') {
	showHead($title);
	showBegin();
}

function showEnd() {
	global $template, $config;
?>
<!-- End Content -->
</div>

<script src="<?=joinPath($config['site_url'],'js/library/jsl.js')?>" type="text/javascript"></script>
<script src="<?=joinPath($config['site_url'],'js/application.js')?>" type="text/javascript"></script>
<?=implode("\n", $template->js_includes);?>
</body>
</html>
<?php }
