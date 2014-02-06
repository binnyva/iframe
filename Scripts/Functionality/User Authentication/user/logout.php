<?php
include("../common.php");

$user->logout();
showMessage("User logged out.", "user/login.php");
