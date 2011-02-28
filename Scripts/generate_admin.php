<?php
require('iframe.php');
error_reporting(0);

if(empty($argv[1])) die("Please provide the name of the table as the first argument");
$table = $argv[1];

$rel = findRelation();
if($rel === false) die("Couldn't find a config file.");
require($rel . 'configuration.php');

$sql = new Sql($config['db_host'], $config['db_user'], $config['db_password'], $config['db_database']);

$field_area = '';
$data_fetches = '';
$table_fields = $sql->getAll("SHOW FIELDS IN " . $table);
foreach($table_fields as $f) {
	$field_name = $f['Field'];
	$length = intval(preg_replace('/\D/','', $f['Type']));
	$data_type = preg_replace('/^([a-z]+).*$/',"$1", $f['Type']); //Get the data type of the field
	$auto_handler = 'off';
	$type = 'text';
	$field_type = 'text';
	$value = "''";
	$forign_table = $field_name;
	
	if($field_name == 'id' and $f['Key'] == 'PRI') {
		$type = 'primary_key';
		continue;
	}
	elseif(($data_type == 'varchar' or $data_type == 'text') and $length == 255) $field_type = 'textarea';
	elseif($data_type == 'longtext' or $data_type == 'mediumtext') $type = 'editor';
	elseif(strpos($data_type,'int')!==false and strpos($f['Type'],'unsigned')!==false) {
		$type = 'int'; //If has an unsigned in it it means that it is a forign key
		$field_type = 'select';
		
		$forign_table = str_replace('_id','',$field_name);
		$value = "\${$forign_table}_list";
		$data_type = 'enum';
		$data_fetches .= "\${$forign_table}_list = \$sql->getById(\"SELECT id,name FROM $forign_table\");\n";
	}
	elseif(strpos($data_type,'enum')!==false) { //Its an enum - but not status.
		if(strpos($field_name,'status')===false) {
			$type = 'list'; //Could be a list or a radio button.
			$vals = preg_replace('/.*\((.+)\)/', "$1", $f['Type']);
			$vals = str_replace("'", "", $vals);
			$all_vals = explode(',', $vals);
			$values = "";
			
			foreach($all_vals as $v) {
				$values .= "'$v'=>'".format($v)."',";
			}
			if($values) $value = 'array('.$values.')';
		} else {
			$field_type = 'checkbox';
			$value_type = 'status';
			$data = array_shift(array_keys($data)); // First element in the enum list is the default value.
			if($data) $value = 'array(\''.implode("','", $data).'\')';
		}
	}
	elseif($data_type == 'datetime') $type = 'datetime';
	elseif($data_type == 'date') $type = 'date';
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

	//$admin->addField("file_path", "File Path", 'varchar',array(), '', 'text');
	if($validation) $validation_string = "array('".implode("','", $validation)."')";
	else $validation_string = "array()";
	
	$field_area .= "\$admin->addField('$field_name', '".format($forign_table)."', '$type', $validation_string, $value, '$field_type', '$data_type');\n";
}

render('/var/www/html/iframe/Scripts/code_templates/generate_admin.php', false, true);

function findRelation() {
	$rel = "";
	$depth = 0;
	while($depth < 10) { //We don't want an infinite loop - do we?
		if(file_exists($rel . "configuration.php")) break;
		else $rel .= "../";
		$depth++;
	}
	if($depth == 10) return false;
	
	return $rel; 
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
