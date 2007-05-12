<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<title><?=$title?></title>
<link href="<?=$abs?>css/style.css" rel="stylesheet" type="text/css" />
<!--[if IE]>
<link rel="stylesheet" href="<?=$abs?>css/style_ie.css" type="text/css" media="all" />
<![endif]-->
<script src="<?=$abs?>js/script.js" type="text/javascript"></script>
<?=$includes?>
</head>
<body>
<div id="header">
<h1 id="logo"><a href="<?=$abs?>"><?=$title?></a></h1>


<!-- Begin Content -->
<?php 
/////////////////////////////////// The Template file will appear here ////////////////////////////

include($GLOBALS['template']->template); 

/////////////////////////////////// The Template file will appear here ////////////////////////////
?>
<!-- End Content -->


</body>
</html>