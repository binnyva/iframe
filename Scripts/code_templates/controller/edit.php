<?php
$queries = array();
foreach($field_names as $fn) {
	$queries[] = '$QUERY[\'' . $fn . '\']';
}
?><?='<'?>?php
include('../common.php');

if(isset($QUERY['action']) and $QUERY['action']=='Edit') {
	if(<?= $object_name ?>->edit($QUERY['id'], <?=implode(', ', $queries)?>)) {
		showMessage("<?=$title?> updated successfully",'index.php');
	}
} else {
	$data = <?=$object_name?>->find($QUERY['id']);
	render();
}
