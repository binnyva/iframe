<form action="" method="post">

<?php
print "<?php\n";
foreach($fields as $f) {
	if($f['auto_handler']) continue;
	
	if($f['type'] == 'text') {
		print '$html->buildInput("'. $f['field'] .'", "'. $f['title'] .'", "'. $f['type'] .'", $data["'. $f['field'] .'"]);';
	
	} elseif($f['type'] == 'password') { //Password and Confirm Password
		print '$html->buildInput("'. $f['field'] .'", "'. $f['title'] .'", "'. $f['type'] .'", "");'."\n";
		print '$html->buildInput("confirm_'. $f['field'] .'", "Confirm Password", "'. $f['type'] .'", "" );';
	
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
		
		print "?>\n<label for='$f[field]'>$f[title]</label>\n<?php\n";
		print '$html->buildDropDownArray($'. $f['field'] .'_list, "'. $f['field'] .'", $data["'. $f['field'] .'"]);print "<br />";';
	}

	print "\n";
}
?>
<?='<'?>?php if($action == "Edit") { ?>
<input type="hidden" name='id' value="<?='<'?>?=$data['id']?>" />
<?='<'?>?php } ?>
<input name="action" value="<?='<'?>?=$action?>" type='submit' />
</form> 
