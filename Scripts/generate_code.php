<?php
include('../common.php');

//:DEBUG:
//print json_encode($PARAM);exit;
//$PARAM = json_decode('{"title":"Task","class_name":"Task","object_name":"$Task","table":"Task","name_single":"Task","name_plural":"Tasks","controller_name":"task","model_file":"Task.php","add_funcionality":"1","edit_funcionality":"1","delete_funcionality":"1","field_name_1":"id","auto_handler_1":"primary_key","field_title_1":"Id","field_type_1":"text","field_date_format_1":"","field_password_encrypt_1":"","field_password_salt_1":"","field_filetype_1":"","list_values_1":"","field_foreign_key_reference_1":"id","field_validation_1":["must","unique"],"field_name_2":"name","auto_handler_2":"off","field_title_2":"Name","field_list_2":"1","field_type_2":"text","field_date_format_2":"","field_password_encrypt_2":"","field_password_salt_2":"","field_filetype_2":"","list_values_2":"","field_foreign_key_reference_2":"name","field_validation_2":["must"],"field_name_3":"description","auto_handler_3":"off","field_title_3":"Description","field_type_3":"textarea","field_date_format_3":"","field_password_encrypt_3":"","field_password_salt_3":"","field_filetype_3":"","list_values_3":"","field_foreign_key_reference_3":"description","field_name_4":"added_on","auto_handler_4":"time_of_insert","field_title_4":"Added On","field_type_4":"text","field_date_format_4":"%d %b %Y, %h:%i %p","field_show_time_4":"1","field_password_encrypt_4":"","field_password_salt_4":"","field_filetype_4":"","list_values_4":"","field_foreign_key_reference_4":"added.on","field_name_5":"completed_on","auto_handler_5":"off","field_title_5":"Completed On","field_type_5":"date","field_date_format_5":"%d %b %Y, %h:%i %p","field_show_time_5":"1","field_password_encrypt_5":"","field_password_salt_5":"","field_filetype_5":"","list_values_5":"","field_foreign_key_reference_5":"completed.on","field_name_6":"status","auto_handler_6":"off","field_title_6":"Status","field_type_6":"list","field_date_format_6":"","field_password_encrypt_6":"","field_password_salt_6":"","field_filetype_6":"","list_values_6":"'working'=>'Working', 'scheduled'=>'Scheduled', 'suspended'=>'Suspended', 'done'=>'Done', ","field_foreign_key_reference_6":"status","field_name_7":"type","auto_handler_7":"off","field_title_7":"Type","field_list_7":"1","field_type_7":"list","field_date_format_7":"","field_password_encrypt_7":"","field_password_salt_7":"","field_filetype_7":"","list_values_7":"'recurring'=>'Recurring','once'=>'Once','scheduled'=>'Scheduled',","field_foreign_key_reference_7":"type","field_name_8":"project_id","auto_handler_8":"off","field_title_8":"Project Id","field_list_8":"1","field_type_8":"foreign_key","field_date_format_8":"","field_password_encrypt_8":"","field_password_salt_8":"","field_filetype_8":"","list_values_8":"","field_foreign_key_reference_8":"Project.id","field_name_9":"user_id","auto_handler_9":"current_user","field_title_9":"User Id","field_type_9":"text","field_date_format_9":"","field_password_encrypt_9":"","field_password_salt_9":"","field_filetype_9":"","list_values_9":"","field_foreign_key_reference_9":"user.id","field_validation_9":["must","unique"],"total_fields":"9","status_field":"status","pager_status":"1","upload_path":"..\/uploads","mandatory_text":"*","main_query":"","generate_files":["model.php","templates\/_form.php","templates\/edit.php","templates\/index.php","templates\/add.php","controllers\/_form.php","controllers\/edit.php","controllers\/index.php","controllers\/add.php","controllers\/delete.php"],"action":"Create Code","error":"","success":""}', true);

$generate_code_mode = true; //Make this false to display the first code template on screen instead of saving it to files(when true) - for debugging purpose.
$generate_code_info = array(); //Global variable - used to pass info from code templates to the code generation scripts.

$details = $PARAM; //I don't like using the param variable - later, we extract the $details array.
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
		'auto_handler'		=> i($PARAM,"auto_handler_$i") ? i($PARAM,"auto_handler_$i") : false,
		
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
	$inserted_index = count($fields) - 1;
	if($fields[$inserted_index]['auto_handler'] == 'off') $fields[$inserted_index]['auto_handler'] = false;
	elseif($fields[$inserted_index]['auto_handler'] == 'status_holder') $status_field = $fields[$inserted_index]['field'];
}

extract($details);

// Some variables that you will be using in the templates.
$primary_key = 'id';
$field_names = array();
$all_field_names = array();
foreach($fields as $f) {
	$all_field_names[] = $f['field'];
	if($f['auto_handler'] === false or $f['auto_handler'] === 'off') $field_names[] = $f['field'];
	else {
		if($f['auto_handler'] === 'primary_key') $primary_key = $f['field'];
	}
}



if(!$generate_code_mode) { //:DEBUG: - this is for debugging purpose only.
	header("Content-type: text/plain");
	$template_index = 3;
	print $folder['code_templates'] . '/' . $PARAM['generate_files'][$template_index] . "\n-------------------------------------------\n";
	
	print getIncludeContents($folder['code_templates'] . '/' . $PARAM['generate_files'][$template_index]);
	exit;
}


// Create the necessary folders.
makeFolder($folder['generated_code']);
makeFolder($folder['generated_code'] . '/templates');
makeFolder($folder['generated_code'] . '/models');

$result = '';
foreach($PARAM['generate_files'] as $template_file) {
	$result .= "Generating $template_file ... ";
	$content = getIncludeContents($folder['code_templates'] . '/' . $template_file);
	
	$type = '';
	$file_name = '';
	
	//Have to seperate the type(folder name) and file name - so that we can insert the controller_name between that.
	if(strpos($template_file, '/') !== false) list($type,$file_name) = explode('/', $template_file);
	
	if($template_file == 'model.php') { // Special Case for models.
		$type = 'models';
		$file = joinPath($folder['generated_code'], $type, $PARAM['model_file']);
		
	} else {
		if(strpos($type,'controller') !== false) $type = ''; //Controllers dont have a folder called controller - they are dumped into the root folder.
		
		makeFolder(joinPath($folder['generated_code'], $type, $PARAM['controller_name']));
		$file = joinPath($folder['generated_code'], $type, $PARAM['controller_name'], $file_name);
	}
	
	$result .= "Done(".strlen($content)."). Saving code to $file ... ";
	
	file_put_contents($file, $content);
	chmod($file, 0777);//Otherwise we will not be able to delete/move the file because the owner is apache/nobody
	
	$result .= "Done<br />";
}


function makeFolder($folder) {
	if(!file_exists($folder)) {
		mkdir($folder, 0777);
		chmod($folder, 0777);
	}
}
function useTemplate($template) {
	global $generate_code_info;
	$content = getIncludeContents($template);
	
	if(isset($generate_code_info['generated_code_path'])) {
		file_put_contents($generate_code_info['generated_code_path'], $content);
		chmod($generate_code_info['generated_code_path'], 0777);//Otherwise we will not be able to delete/move the file because the owner is apache/nobody
	}
}

render();
