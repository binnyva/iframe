<?='<'?>?php
include('../common.php');
include('../includes/classes/SqlPager.php');

//The pager.
$pager = new SqlPager("SELECT <?= implode(',',$Model['fields']) ?> FROM <?= $Model['table'] ?>");
$<?= $Controller['name_plural'] ?>_sql = $pager->getSql();
$<?= $Controller['name_plural'] ?> = array();
while($parts = $sql->fetchRow($<?= $Controller['name_plural'] ?>_sql)) {
	$projects[] = $parts;
}

render();