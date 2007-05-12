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
