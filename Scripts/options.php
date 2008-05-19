<?php
include('../common.php');

$html = new HTML;

//if(!isset($argv[1])) {print "Please provide the name of the table as the first argument\n";exit;}
//$table = $argv[1];

$table = $PARAM['table'];
if(isset($table)) {
	$Fields = array();

	$table_fields = $sql->getAll("SHOW FIELDS IN " . $table);
	foreach($table_fields as $f) {
		$values = '';
		$field_name = $f['Field'];
		$data_type = preg_replace('/^([a-z]+).*$/',"$1", $f['Type']); //Get the data type of the field
		$auto_handle = 'off';
		$type = 'text';
		
		if($field_name == 'id' and $f['Key'] == 'PRI') {
			$type = 'primary_key';
			$auto_handle = 'primary_key';
		}
		elseif($data_type == 'varchar' or $data_type == 'text') $type = 'text';
		elseif($data_type == 'longtext' or $data_type == 'mediumtext') $type = 'editor';
		elseif(strpos($data_type,'int')!==false and strpos($f['Type'],'unsigned')!==false) $type = 'list'; //If has an unsigned in it it means that it is a forign key
		elseif(strpos($data_type,'enum')!==false and strpos($field_name,'status')===false) { //Its an enum - but not status.
			$type = 'list'; //Could be a list or a radio button.
			$vals = preg_replace('/.*\((.+)\)/', "$1", $f['Type']);
			$vals = str_replace("'", "", $vals);
			$all_vals = explode(',', $vals);
			$values = "";
			
			foreach($all_vals as $v) {
				$values .= "'$v'=>'".format($v)."',";
			}
		}
		elseif($data_type == 'datetime') {
			if($field_name=='added_on' or $field_name=='created_on' or $field_name=='inserted_on') {
				$auto_handle = 'time_of_insert';
				
			} elseif($field_name=='edited_on' or $field_name=='updated_on' or $field_name=='modified_on') {
				$auto_handle = 'time_of_update';
			
			}
			
			$type = 'datetime';
		}
		elseif($data_type == 'date') $type = 'date';
		elseif((($data_type == 'enum' or $data_type == 'int' or $data_type == 'smallint') and $field_name == 'status')) {
			$field_data['status_funcionality'] = 1;
			$auto_handle = 'status_holder';
		}
		else $type = 'text';


		
		//Try to guess all the validation rules
		$validation = array();
		if($field_name == 'name' or $field_name == 'title') $validation[] = 'must';
		if(strpos($field_name,'email') !== false) $validation[] = 'email';
		if($data_type == 'date') $validation[] = 'date';
		if($data_type == 'datetime') $validation[] = 'datetime';
		if(strpos($field_name,'username') !== false or strpos($field_name,'login') !== false) {
			$validation[] = 'must';
			$validation[] = 'unique';
		}
		
		if(findWord($field_name, array('age','year','price','count'))) $validation[] = 'number';
		if(strpos($field_name,'password') !== false) $validation[] = 'password';
		///:TODO: Url, Phone, Address, Age, 

		$Fields[] = array(
			'name'			=> $field_name,
			'validation'	=> $validation,
			'type'			=> $type,
			'info'			=> $f,
			'data_type'		=> $data_type,
			'values'		=> $values,
			'auto_handle'	=> $auto_handle,
		);
	}
}

$field_data = array();
if(!$_POST) {
	$field_data['title'] = format($table);
	$field_data['file'] = strtolower($table) . '.php';
	$field_data['table'] = $table;
	$field_data['name_single'] = $field_data['title'];
	
	$field_data['add_funcionality'] = 1;
	$field_data['edit_funcionality'] = 1;
	$field_data['delete_funcionality'] = 1;
	$field_data['status_funcionality'] = 0;
	
	$field_count = 1;
	foreach($Fields as $f) {
		$list = 0;
		if(findWord($f['name'], array('name','username','login','title','description','url','website'))) $list = 1; //Stuff that must be listed.
		
		$date_format = '';
		if($f['type'] == 'date') $date_format = '%d %b %Y';
		elseif($f['type'] == 'datetime') $date_format = '%d %b %Y, %h:%i %p';
		
		$filetypes = '';
		if(findWord($f['name'], array('image','pic','photo','img','logo'))) $filetypes = 'jpg,jpeg,png,gif';
		
		$field_data["field_title_$field_count"]				= format($f['name']);
		$field_data["field_name_$field_count"]				= $f['name'];
		$field_data["field_list_$field_count"]				= $list;
		$field_data["field_type_$field_count"]				= $f['type'];
		
		$field_data["field_date_format_$field_count"]		= $date_format;
		$field_data["field_show_time_$field_count"]			= ($f['type'] == 'datetime') ? 1 : 0;
		$field_data["field_password_encrypt_$field_count"]	= '';
		$field_data["field_password_salt_$field_count"]		= '';
		$field_data["field_filetype_$field_count"]			= $filetypes;
		$field_data["list_values_$field_count"]				= $f['values'];
		$field_data["field_validation_$field_count"]		= $f['validation'];
		$field_data["field_auto_handle_$field_count"]		= $f['auto_handle'];
		$field_data["field_foreign_key_reference_$field_count"]		= str_replace('_', '.', $f['name']);
		$field_count++;
	}
	$total_fields = $field_count - 1;
	
} else {
	$field_data = $_POST;
}

function findWord($needle, $haystack) {
	foreach($haystack as $str) {
		if(preg_match("/\b$needle\b/i", $str)) return true;
	}
	return false;
}
function findString($needle, $haystack) {
	foreach($haystack as $str) {
		if(stripos($str, $needle) !== false) return true;
	}
	return false;
}

$template->addResource('http://localhost/iframe/js/plugins/jsl_debug.js','js',1);
render();
