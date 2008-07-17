<?php
include('../common.php');

//print json_encode($PARAM);exit;
//:DEBUG:
//$PARAM = json_decode('{"title":"Task","class_name":"Task","object_name":"$Task","table":"Task","name_single":"Task","name_plural":"Tasks","controller_name":"task","model_file":"Task.php","add_funcionality":"1","edit_funcionality":"1","delete_funcionality":"1","status_funcionality":"1","field_name_1":"id","field_auto_handle_1":"primary_key","field_title_1":"Id","field_type_1":"text","field_date_format_1":"","field_password_encrypt_1":"","field_password_salt_1":"","field_filetype_1":"","list_values_1":"","field_foreign_key_reference_1":"id","field_validation_1":["must","unique"],"field_name_2":"name","field_title_2":"Name","field_list_2":"1","field_type_2":"text","field_date_format_2":"","field_password_encrypt_2":"","field_password_salt_2":"","field_filetype_2":"","list_values_2":"","field_foreign_key_reference_2":"name","field_validation_2":["must"],"field_name_3":"description","field_title_3":"Description","field_type_3":"textarea","field_date_format_3":"","field_password_encrypt_3":"","field_password_salt_3":"","field_filetype_3":"","list_values_3":"","field_foreign_key_reference_3":"description","field_name_4":"type","field_title_4":"Type","field_type_4":"list","field_date_format_4":"","field_password_encrypt_4":"","field_password_salt_4":"","field_filetype_4":"","list_values_4":"\'Idea\'=>\'Idea\',\'Immediately\'=>\'Immediately\',\'Someday\/Maybe\'=>\'Someday\/Maybe\',\'Waiting\'=>\'Waiting\',\'This Week\'=>\'This Week\',\'This Month\'=>\'This Month\',\'This Year\'=>\'This Year\',\'Done\'=>\'Done\',\'Misc\'=>\'Misc\',","field_foreign_key_reference_4":"type","field_name_5":"url","field_title_5":"Url","field_list_5":"1","field_type_5":"text","field_date_format_5":"","field_password_encrypt_5":"","field_password_salt_5":"","field_filetype_5":"","list_values_5":"","field_foreign_key_reference_5":"url","field_name_6":"created_on","field_auto_handle_6":"time_of_insert","field_title_6":"Created On","field_type_6":"text","field_date_format_6":"%d %b %Y, %h:%i %p","field_show_time_6":"1","field_password_encrypt_6":"","field_password_salt_6":"","field_filetype_6":"","list_values_6":"","field_foreign_key_reference_6":"created.on","field_name_7":"edited_on","field_auto_handle_7":"time_of_update","field_title_7":"Edited On","field_type_7":"text","field_date_format_7":"%d %b %Y, %h:%i %p","field_show_time_7":"1","field_password_encrypt_7":"","field_password_salt_7":"","field_filetype_7":"","list_values_7":"","field_foreign_key_reference_7":"edited.on","field_name_8":"completed_on","field_title_8":"Completed On","field_type_8":"date","field_date_format_8":"%d %b %Y, %h:%i %p","field_show_time_8":"1","field_password_encrypt_8":"","field_password_salt_8":"","field_filetype_8":"","list_values_8":"","field_foreign_key_reference_8":"completed.on","field_name_9":"due_on","field_title_9":"Due On","field_type_9":"date","field_date_format_9":"%d %b %Y, %h:%i %p","field_show_time_9":"1","field_password_encrypt_9":"","field_password_salt_9":"","field_filetype_9":"","list_values_9":"","field_foreign_key_reference_9":"due.on","field_name_10":"project_id","field_title_10":"Project","field_type_10":"foreign_key","field_date_format_10":"","field_password_encrypt_10":"","field_password_salt_10":"","field_filetype_10":"","list_values_10":"","field_foreign_key_reference_10":"Project.id","field_name_11":"sort_order","field_title_11":"Sort Order","field_list_11":"1","field_type_11":"text","field_date_format_11":"","field_password_encrypt_11":"","field_password_salt_11":"","field_filetype_11":"","list_values_11":"","field_foreign_key_reference_11":"sort.order","field_validation_11":["number"],"field_name_12":"file","field_title_12":"File","field_type_12":"text","field_date_format_12":"","field_password_encrypt_12":"","field_password_salt_12":"","field_filetype_12":"","list_values_12":"","field_foreign_key_reference_12":"file","field_name_13":"status","field_auto_handle_13":"status_holder","field_title_13":"Status","field_type_13":"text","field_date_format_13":"","field_password_encrypt_13":"","field_password_salt_13":"","field_filetype_13":"","list_values_13":"","field_foreign_key_reference_13":"status","total_fields":"14","action":"Create Code"}', true);

$details = array();
$details['title'] = $PARAM['title'];
$details['class_name'] = $PARAM['class_name'];
$details['object_name'] = $PARAM['object_name'];

$details['table'] = $PARAM['table'];
$details['name_single'] = $PARAM['name_single'];
$details['name_plural'] = $PARAM['name_plural'];

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
		'forign_key_ref'=> i($PARAM,"field_foreign_key_reference_$i"),
		'list_values'	=> i($PARAM,"list_values_$i"),
	);
}

extract($details);

// Some variables that you will be using in the templates.
$primary_key = 'id';
$field_names = array();
$all_fields_names = array();
foreach($fields as $f) {
	$all_field_names[] = $f['field'];
	if($f['auto_handler'] === false) $field_names[] = $f['field'];
	else {
		if($f['auto_handler'] === 'primary_key') $primary_key = $f['field'];
	}
}

header("Content-type:text/plain");
include('code_templates/model.php');
//include('code_templates/template/_form.php');

