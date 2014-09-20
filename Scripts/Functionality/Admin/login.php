<?php
require('./common.php');

if(i($QUERY,'action') == 'Login') {
	// $admin = $sql->getOne("SELECT id,name FROM Admin WHERE 
	// 							username='".$sql->escape($QUREY['username'])."' 
	// 							AND password='".$sql->escape($QUREY['password'])."' 
	// 							AND status='1'");
	$admin = array('id'=>1,'name'=>"Chuck Norris");

	if($admin) {
		$_SESSION['admin_id'] = $admin['id'];
		$_SESSION['admin_name'] = $admin['name'];
	} else {
		showMessage("Login Error. Invalid username/password.",  "login.php", "error");
	}
} elseif(i($QUERY,'action') == 'Send Password') {
	$admin = $sql->getOne("SELECT id,name,username,password,email FROM Admin WHERE 
								username='".$sql->escape($QUREY['username'])."' 
								OR email='".$sql->escape($QUREY['username'])."'");

	if($admin) {
		mail($admin['email'], "Password for $config[site_title]", <<<END
Hi $admin[name],

Someone(hopefully you) requested that we send your password to the email you have chosen. 
So here is the login details for your account at $config[site_title]...

Username : $admin[username]
Password : $admin[password]

Thanks,
$config[site_title] Team
END
);

		showMessage("An email containing your login details has been sent to your email address", "login.php", "success");
	} else {
		showMessage("We don't have anyone with that username/email",  "login.php", "error");
	}
}

$template->options['layout_file']	= 'templates/layout/login.php';
$template->template = '';
render();
