<?php
require_once(joinPath($config['site_folder'] , 'models/User.php'));
$user = new User;
if(strpos($config['PHP_SELF'], '/user/') === false) checkUser();
if(strpos($config['PHP_SELF'], '/user/profile.php') !== false) checkUser();


function checkUser($check_admin = false) {
	global $config;
	
	if((!isset($_SESSION['user_id']) or !$_SESSION['user_id']))
		showMessage("Please login to use this feature", $config['site_home'] . 'user/login.php', "error");
}
