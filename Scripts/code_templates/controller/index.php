<?='<'?>?php
include('../common.php');
include('../includes/classes/SqlPager.php');

//The pager.
$pager = new SqlPager("SELECT <?= implode(',', $all_fields_names) ?>) ?> FROM <?= $table ?>");
$<?=strtolower($name_plural)?>_sql = $pager->getSql();
$<?=strtolower($name_plural)?> = array();
while($parts = $sql->fetchRow($<?=strtolower($plural)?>_sql)) {
	$<?=strtolower($name_plural)?>[] = $parts;
}

render();