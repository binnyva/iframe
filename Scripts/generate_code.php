<?php
include('../common.php');

$details = array();
$details['title'] = $PARAM['title'];
$details['class_name'] = str_replace(' ', '', $details['title']);
$details['table'] = $PARAM['table'];
$details['name_single'] = $PARAM['name_single'];

$details['functionality'] = array(
		'add'	=> i($PARAM, 'add_funcionality'),
		'edit'	=> i($PARAM, 'edit_funcionality'),
		'delete'=> i($PARAM, 'delete_funcionality'),
		'status'=> i($PARAM, 'status_funcionality')
	);

$fields = array();

for($i=1; $i<$PARAM['total_fields']; $i++) {
	if(!isset($PARAM["field_name_$i"]) or !$PARAM["field_name_$i"]) continue;
	
	$fields[] = array(
		'field'			=> $PARAM["field_name_$i"],
		'auto_handler'	=> i($PARAM,"field_auto_handle_$i") ? i($PARAM,"field_auto_handle_$i") : false,
		
		'title'			=> i($PARAM,"field_title_$i"),
		'list'			=> i($PARAM,"field_list_$i") ? true : false,
		'type'			=> i($PARAM,"field_type_$i"),
		'validation'	=> i($PARAM,"field_validation_$i", array()),
		
		'date_format'	=> i($PARAM,"field_date_format_$i"),
		'show_time'		=> i($PARAM,"field_show_time_$i"),
		'password_encryption_algorithm'	=> i($PARAM,"field_password_encrypt_$i"),
		'password_salt'	=> i($PARAM,"field_password_salt_$i"),
		'filetype'		=> i($PARAM,"field_filetype_$i"),
		'values'		=> i($PARAM,"field_values_$i"),
		'forign_key_ref'=> i($PARAM,"field_foreign_key_reference_$i")
	);
}

extract($details);
include('code_templates/model.php');

//print get_include_contents('code_templates/model.php');

function get_include_contents($filename) {
	global $fields,$details;
	extract($details);
	
	if (is_file($filename)) {
		ob_start();
		include $filename;
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	return false;
}
