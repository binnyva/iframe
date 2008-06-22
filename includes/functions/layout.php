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

//////////////////////// Layout Functions - DEPRECATED ///////////////////
function showHead($title='') {
	global $template, $config;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<title><?=$title?></title>
<link href="<?=joinPath($config['site_absolute_url'],'/')?>css/style.css" rel="stylesheet" type="text/css" />
<?=implode($template->css_includes,"\n");?>
<?php
}


function showBegin() {
	global $config;
	if(isset($config['site_title'])) {
?>
</head>
<body>
<div id="header">
<h1 id="logo"><a href="<?=$config['site_absolute_url']?>"><?=$config['site_title']?></a></h1>
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

<script src="<?=joinPath($config['site_absolute_url'],'/')?>js/jsl.js" type="text/javascript"></script>
<script src="<?=joinPath($config['site_absolute_url'],'/')?>js/application.js" type="text/javascript"></script>
<?=implode($template->js_includes,"\n");?>
</body>
</html>
<?php }
