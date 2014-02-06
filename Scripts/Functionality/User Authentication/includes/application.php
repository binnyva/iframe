<?php
require_once(joinPath($config['site_folder'] , 'models/User.php'));
$user = new User;

function checkUser($check_admin = false) {
	global $config;
	
	if((!isset($_SESSION['user_id']) or !$_SESSION['user_id']))
		showMessage("Please login to use this feature", $config['site_url'] . 'user/login.php', "error");
}
