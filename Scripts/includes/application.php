<?php
// The Globals...
$available_templates = array(
	'model.php', 
	'templates/_form.php', 'templates/edit.php', 'templates/index.php', 'templates/add.php',
	'controllers/_form.php', 'controllers/edit.php', 'controllers/index.php', 'controllers/add.php', 'controllers/delete.php',
);

$folder = array(
	'code_templates'	=>	'code_templates',
	'generated_code'	=>	'GeneratedCode'
);


// The Functions...

function getIncludeContents($filename) {
	extract($GLOBALS);
	
	if (is_file($filename)) {
		ob_start();
		include($filename);
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	return false;
}
