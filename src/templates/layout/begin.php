</head>
<body>
<div id="header">
<h1 id="logo"><a href="<?php echo iframe\App::$config['app_url'] ?> ?>"><?php echo iframe\App::$config['app_name'] ?></a></h1>
</div>

<div id="content">
<div id="error-message"><?php
	if(isset($PARAM['error'])) print strip_tags($PARAM['error']); //It comes from the URL
	elseif(isset($QUERY['error'])) print $QUERY['error']; //Its set in the code(validation error or something.
?></div>
<div id="success-message" <?php echo !empty($QUERY['success']) ? '':'style="display:none;"';?>><?php if(!empty($QUERY['success'])) echo strip_tags(stripslashes($QUERY['success'])); ?></div>
