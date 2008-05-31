<?='<'?>?php
include('../common.php');
include('../includes/classes/SqlPager.php');

//The pager.
$pager = new SqlPager("SELECT <?= implode(',', $all_field_names) ?>) ?> FROM <?= $table ?>");
$<?=strtolower($plural)?>_sql = $pager->getSql();
$<?=strtolower($plural)?> = array();
while($parts = $sql->fetchRow($<?=strtolower($plural)?>_sql)) {
	$<?=strtolower($plural)?>[] = $parts;
}

render();