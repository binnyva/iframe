<?php
require('../common.php');
$config['site_folder'] = dirname(__FILE__);
$template->page = str_replace("admin/", "", $template->page);
$template->css_folder = 'admin/css';
$template->js_folder = 'admin/js';
$template->template = 'None';

$default_city_id = 1;

if(!isset($_GET['city_id'])) $_GET['city_id'] = $default_city_id;
if(!isset($_GET['stauts']))$_GET['status'] = 1;


if(empty($_SESSION['admin_id'])) {
	if($template->page != 'login.php') {
		showMessage("Please login to continue...", "admin/login.php", "error");
	}
}
