<form action="" method="post" id="<?=$PARAM['controller_name']?>-form" class="form-area">

<?php
print "<?php\n";
foreach($fields as $f) {
	if($f['auto_handler']) continue;
	
	if($f['type'] == 'password') { //Password and Confirm Password
		print '$html->buildInput("'. $f['field'] .'", "'. $f['title'] .'", "'. $f['type'] .'", "");'."\n";
		print '$html->buildInput("confirm_'. $f['field'] .'", "Confirm Password", "'. $f['type'] .'", "" );';
	
	} elseif($f['type'] == 'date') {
		print '$html->buildInput("'. $f['field'] .'", "'. $f['title'] .'", "text", i($data,"'. $f['field'] .'"));';
	
	} elseif($f['type'] == 'list' or $f['type'] == 'foreign_key') { //If it a list, create a dropdown making code with foreign refenences.
		print '$html->buildInput("'. $f['field'] .'", "'. $f['title'] .'", "select", i($data,"'. $f['field'] .'"), array("options"=>$'. $f['field'] .'_list));';
	
	} else {
		print '$html->buildInput("'. $f['field'] .'", "'. $f['title'] .'", "'. $f['type'] .'", i($data,"'. $f['field'] .'"));';
	
	} 

	print "\n";
}
?>

if($action == "Edit") { ?>
<input type="hidden" name='id' value="<?='<'?>?=$data['id']?>" />
<?='<'?>?php } ?>
<input name="action" value="<?='<'?>?=$action?>" type='submit' />
</form> 
