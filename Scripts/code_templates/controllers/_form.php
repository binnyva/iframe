<?="<";?>?php
$html = new HTML;

$template->addResource('libraries/check.js','js');

<?php
foreach($fields as $f) {
	if($f['auto_handler']) continue;
	
	if($f['type'] == 'date') {
	?>
$template->addResource('libraries/calendar.js','js');
$template->addResource('libraries/calendar.css','css');
<?php
useTemplate('js/form.js.php');
print "\$template->addResource('$PARAM[controller_name]/form.js','js');\n";
	
	} elseif($f['type'] == 'list' or $f['type'] == 'foreign_key') { //If it a list, create a dropdown making code with foreign refenences.
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
