<?php
include('../common.php');
include('../includes/classes/HTML.php');

$template->options['template_folder'] = 'Scripts/templates';
$template->options['insert_layout'] = false;
$template->setTemplate('index.php');

$Controller = array();
$Model = array();

//if(!isset($argv[1])) {print "Please provide the name of the table as the first argument\n";exit;}
//$Model['table'] = $argv[1];

$Model['table'] = 'User';
if(isset($Model['table'])) {
	$Model['object_name'] = $Model['table'];
	$Controller['name'] = format($Model['table']); //'BadGuy' becomes 'Bad Guy'
	$Controller['name_plural'] = $Controller['name'] . 's';// :TODO: ies, es, 	etc.

	$Fields = array();

	$table_fields = $sql->getAll("SHOW FIELDS IN " . $Model['table']);
	foreach($table_fields as $f) {
		if(!isset($Model['primary_key'])) {
			if($f['Key'] == 'PRI') $Model['primary_key'] = $f['Field'];
		}
		
		$data_type = preg_replace('/^([a-z]+).*$/',"$1", $f['Type']); //Get the data type of the field
		if($data_type == 'varchar' || $data_type == 'text') $type = 'text';
		elseif($data_type == 'longtext' || $data_type == 'mediumtext') $type = 'editor';
		elseif(strpos($data_type,'int')!==false and strpos($f['Type'],'unsigned')!==false) $type = 'list'; //If has an unsigned in it it means that it is a forign key
		elseif($data_type == 'datetime' || $data_type == 'date') $type = 'date';
		else $type = 'text';
		
		//Try to guess all the validation rules
		$validation = array();
		if(strpos($f['Field'],'email') !== false) $validation[] = 'email';
		if($data_type == 'date') $validation[] = 'date';
		if($data_type == 'datetime') $validation[] = 'datetime';
		if(strpos($f['Field'],'username') !== false || strpos($f['Field'],'login') !== false) {
			$validation[] = 'must';
			$validation[] = 'unique';
		}
		
		if(preg_match('/\bage\b/',$f['Field']) || preg_match('/\byear\b/',$f['Field']) ) $validation[] = 'number';
		if(strpos($f['Field'],'password') !== false) $validation[] = 'password';
		///:TODO: Url, Phone, Address, Age, 

		$Fields[] = array(
			'name'			=> $f['Field'],
			'validation'	=> $validation,
			'type'			=> $type,
			'info'			=> $f,
			'data_type'		=> $data_type
		);
	}
}

$field_data = array();
if(!$_POST) {
	$field_data['title'] = $Controller['name'];
	$field_data['file'] = '';
	$field_data['table'] = $Model['table'];
	$field_data['single'] = $Controller['name_plural'];
	$field_data['primary_key'] = $Model['primary_key'];
	
	$field_data['add_funcionality'] = 1;
	$field_data['edit_funcionality'] = 1;
	$field_data['delete_funcionality'] = 1;
	$field_data['status_funcionality'] = 1;
	
	$field_data['fields'] = array();
	$field_count = 1;
	foreach($Fields as $f) {
		//Some fields dont have to be shown in the page...
		if(($f['data_type'] == 'int' and $f['info']['Key'] == 'PRI')  //Primary Key - Skip
			or ($f['data_type'] == 'datetime' and ($f['name'] == 'added_on' or $f['name'] == 'created_on' //Timestamping
				or $f['name'] == 'edited_on' or $f['name'] == 'modified_on'))
			or (($f['data_type'] == 'enum' or $f['data_type'] == 'int' or $f['data_type'] == 'smallint') and $f['name'] == 'status')
		) continue;

		$list = 0;
		if(findString($f['name'], array('name','username','login',
				'title','description','url','website'))) $list = 1;
		$date_format = '';
		if($f['type'] == 'date') $date_format = '%d %b %Y';
		elseif($f['type'] == 'datetime') $date_format = '%d %b %Y, %h:%i %p';
		
		$filetypes = '';
		if(findString($f['name'], array('image','pic','photo','img','logo'))) $filetypes = 'jpg,jpeg,png,gif';
		
		$field_data["field_title_$field_count"]				= format($f['name']);
		$field_data["field_name_$field_count"]				= $f['name'];
		$field_data["field_list_$field_count"]				= $list;
		$field_data["field_date_format_$field_count"]		= $date_format;
		$field_data["field_show_time_$field_count"]			= ($f['type'] == 'datetime') ? 1 : 0;
		$field_data["field_password_encrypt_$field_count"]	= '';
		$field_data["field_password_salt_$field_count"]		= '';
		$field_data["field_filetype_$field_count"]			= $filetypes;
		$field_data["list_values_$field_count"]				= '';
		$field_data["field_validation_$field_count"]		= $f['validation'];
		
		$field_count++;
	}
	$total_fields = $field_count - 1;
	
} else {
	$field_data = $_POST;
}
//print get_include_contents('templates/model.php');

function get_include_contents($filename) {
	global $Fields,$Model,$Controller;
	if (is_file($filename)) {
		ob_start();
		include $filename;
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	return false;
}

function findString($needle, $haystack) {
	foreach($haystack as $str) {
		if(stripos($str, $needle) !== false) return true;
	}
	return false;
}

render();
