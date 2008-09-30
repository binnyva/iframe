<?php
makeFolder(joinPath($folder['generated_code'], 'js', $PARAM['controller_name']));
$GLOBALS['generate_code_info']['generated_code_path'] = joinPath($folder['generated_code'], 'js', $PARAM['controller_name'], 'form.js');

?>
// This will automatically be called on page load.
function init() {
<?php
foreach($fields as $f) {
	if($f['auto_handler']) continue;
	
	if($f['type'] == 'date') {
		?>
calendar.init({ 'input':'<?=$f['field']?>'<?php
if($f['show_time']) {
	print ", 'show_time':true"; //This is not yet supported by our library.
}
?> });

		<?php
	}
}
?>

// Validation rules
JSL.dom(<?=$PARAM['controller_name']?>-form").submit(function(e) {
	if(!check(<?='<?='?> json_encode($object_name->getValidationRules()) ?>)) {
		JSL.event(e).stop();
	}
?>
});
}
