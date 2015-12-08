<?php
function render($file='', $use_layout=true, $use_exact_path = false) {
	//If it is an ajax request, we don't have to render the page.
	if(isset($_REQUEST['ajax'])) {
		print '{"success":"Done","error":false}';
		return;
	}

	//Otherwise, render it.
	$GLOBALS['template']->render($file, $use_layout, $use_exact_path);
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
<!DOCTYPE html>
<html lang="en"><head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $title; ?></title>
<link href="<?php echo joinPath($config['site_url'],'css/style.css')?>" rel="stylesheet" type="text/css" />
<link href="<?php echo joinPath($config['site_url'],'images/silk_theme.css')?>" rel="stylesheet" type="text/css" />
<link href="<?php echo $config['site_url'] ?>bower_components/bootstrap/dist/css/bootstrap.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $config['site_url'] ?>bower_components/bootstrap/dist/css/bootstrap-theme.css" rel="stylesheet" type="text/css" />
<?php echo implode("\n", $template->css_includes);?>
<?php
}


function showBegin() {
	global $config, $QUERY;
?>
</head>
<body>
<div id="loading">loading...</div>

<?php if(isset($config['site_title'])) { ?>
<div id="header" class="navbar navbar-inverse navbar-fixed-top" role="navigation">
<div id="nav" class="container">
	<div class="navbar-header">
	  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
	    <span class="sr-only">Toggle navigation</span>
	    <span class="icon-bar"></span>
	    <span class="icon-bar"></span>
	    <span class="icon-bar"></span>
	  </button>
	  <a class="navbar-brand"  href="<?php echo $config['site_home']?>"><?php echo $config['site_title']?></a>
	</div>
	<div class="collapse navbar-collapse">
	</div>
</div>
</div><br /><br /><br />
<?php } ?>

<div id="content" class="container">
<div id="error-message" <?php echo ($QUERY['error']) ? '':'style="display:none;"';?>><?php
	if(isset($PARAM['error'])) print strip_tags($PARAM['error']); //It comes from the URL
	else print $QUERY['error']; //Its set in the code(validation error or something.
?></div>
<div id="success-message" <?php echo ($QUERY['success']) ? '':'style="display:none;"';?>><?php echo strip_tags(stripslashes($QUERY['success']))?></div>
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

<script src="<?php echo joinPath($config['site_url'],'js/library/jquery.js')?>" type="text/javascript"></script>
<script src="<?php echo joinPath($config['site_url'],'js/application.js')?>" type="text/javascript"></script>
<?php echo implode("\n", $template->js_includes);?>
</body>
</html>
<?php }
