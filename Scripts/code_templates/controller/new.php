<?php
$queries = array();
foreach($field_names as $fn) {
	$queries[] = '$QUERY[\'' . $fn . '\']';
}
?><?='<'?>?php
include('../common.php');

if(isset($QUERY['action']) and $QUERY['action']=='Create') {
	if($id = <?= $object_name ?>->create(<?=implode(', ', $queries)?>)) {
		showMessage("<?= $title ?> created successfully","index.php",'success',$id);
	}
}

render();