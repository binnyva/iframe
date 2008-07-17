<?php
function get_include_contents($filename) {
	global $fields,$details;
	extract($details);
	
	if (is_file($filename)) {
		ob_start();
		include $filename;
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	return false;
}
