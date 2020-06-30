</head>
<body>
<div id="header">
<h1 id="logo"></h1>
</div>

<div id="content">
<div id="error-message"><?php
	if(isset($PARAM['error'])) print strip_tags($PARAM['error']); //It comes from the URL
	elseif(isset($QUERY['error'])) print $QUERY['error']; //Its set in the code(validation error or something.
?></div>
<div id="success-message" <?php echo !empty($QUERY['success']) ? '':'style="display:none;"';?>><?php 
  if(!empty($QUERY['success'])) echo strip_tags(stripslashes($QUERY['success'])); 
?></div>

<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
  <a class="navbar-brand" href="<?php echo iframe\App::$config['app_url'] ?>"><?php echo iframe\App::$config['app_name'] ?></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-main" aria-controls="navbar-main" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbar-main">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="<?php echo iframe\App::$config['app_url'] ?>">Home <span class="sr-only">(current)</span></a>
      </li>
    </ul>
  </div>
</nav>

<main role="main" class="container">