<?php 
//10 has more priority than 1
function error($msg, $file="", $line="", $priority=5) {
	global $config,$abs;
	
	if($config['mode'] == 'd' or $config['mode'] == 't') {
		print <<<END
<link href="${abs}css/error.css" type="text/css" rel="stylesheet" />
<div class="error-message priority$priority">
<h1>Error!</h1>
<div id="message">$msg</div><br />
END;

		if($file and $line) {
			$line = $line - 1;
			print "In file '$file' at line $line..<br /><pre>";
			
			//Get the 5 lines surronding the error lines - before and after
			$lines = explode("\n",file_get_contents($file));
			for($i=$line-5; $i<$line+5; $i++) {
				if($i == $line) print '<span class="error-line">';
				print "\n<span class='line-number'>$i)</span> ";
				print str_replace(
					array('<',"\t"),
					array('&lt;','  '),
					$lines[$i]
				);//Trim it?
				if($i == $line) print '</span>';
			}
			print '</pre>';
		}
		print '</div>';
		exit();
	} else {
		if($priority >= 10) die($msg);
	}
}

function showMessage($message, $url="?", $status="success",$id=0) {
	//If it is an ajax request, Just print the data
	if(isset($_REQUEST['ajax'])) {
		$success = '';
		$error = '';
		$insert_id = '';

		if($status == 'success') $success = $message;
		if($status == 'error') $error = $message;
		if($id) $insert_id = ',"id":'.$id;

		print '{"success":"'.$success.'","error":"'.$error.'"'.$insert_id.'}';
	} else {
		if(strpos($url,'?') === false) $url .= '?';
		header("Location:$url&$status=".urlencode($message));
	}
	exit;
}

/**
 * Read the plugin folder and put all the plugins found there in the dropdown menu
 */
function loadPlugins() {
	global $rel,$abs;
	
	$plugins = array();
	// Open plugin directory, and proceed to read its contents
	$dir = $rel.'plugins/';
	$files = ls($dir);
	foreach($files as $file) {
		if($file == 'CVS' || $file == '.' || $file =='..' || $file =='api' || $file == '.svn'
		|| filetype($dir . $file) != 'dir') continue;
		else
			$plugins[] = $file;
	}
	
	//Show the dropdown menu only if there are plugins
	if(count($plugins)) {
		print '<li class="dropdown"><a href="'.$rel.'plugins/" class="plugin with-icon">Plugins</a>';

		print "\n<ul class='menu-with-icon plugins'>\n";
		foreach($plugins as $plug) {
			print '<li><a href="'.$abs.'plugins/'.$plug.'">' . format($plug) . '</a></li>'."\n";
		}
		print '</ul></li>';
	}
}

/**
 * Try to find the project of the given task. Searches for the name of all the 
 *  projects in the text. If any project is found, the function returns the id of that project
 */
function findProject($text) {
	$text = strtolower($text);
	global $projects;
	
	$project_id = 0;
	$biggest_count = 0;
	$words = preg_split("/[\s,\.]/",strtolower($text));
	
	$remove_words = array('the','in','a','i','of','if'); //Add more later.
	$words = array_values(array_diff($words, $remove_words));
	
	foreach($projects as $id=>$name) {
		$count = 0;
		foreach($words as $word) {
			if(empty($word)) continue;
			$count += substr_count(strtolower($name),$word);
		}
		if($count > $biggest_count) {
			$project_id = $id;
			$biggest_count = $count;
		}
	}
	return $project_id;
}
