<?='<'?>?php
include('../common.php');

<?php
foreach($fields as $f) {
	if($f['type'] == 'list' or $f['type'] == 'foreign_key') { //If it a list, get the foreign refenences.
		print '$'. $f['field'] .'_list = ';
		
		if($f['type'] == 'foreign_key') {
			list($fk_table, $fk_id) = explode('.', $f['forign_key_ref']);
			print '$sql->getById("SELECT '.$fk_id.',name FROM '.$fk_table.'");';
			
		} elseif(preg_match('/^\s*SELECT/', $f['list_values']) ) {
			print '$sql->getById("'.$f['list_values'].'")';
		
		} else {
			print 'array(' . $f['list_values'] . ");\n";
		}
	}
}
?>


//The pager...
$pager = new SqlPager("<?php
if($main_query) print $main_query;
else {
	print "SELECT " . implode(',', $all_field_names) . " FROM " . $table;
	if($functionality['status'] and $status_field) {
		print " WHERE $status_field='1'";
	}
	print '"';
}
?>, <?=$pager_items_per_page?>);
$<?=strtolower($name_plural)?> = $pager->getPage();

render();