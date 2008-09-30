<?='<'?>?php
include('../common.php');

//The pager.
$pager = new SqlPager("SELECT <?= implode(',', $all_field_names) ?> FROM <?= $table ?><?php

if($functionality['status'] and $status_field) {
	print " WHERE $status_field='1'";
}
?>");
$<?=strtolower($name_plural)?> = $pager->getPage();

render();