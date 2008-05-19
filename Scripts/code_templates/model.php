<?php
header("Content-type:text/plain");
$field_names = array();
foreach($fields as $f) {
	if($f['auto_handler'] === false)
		$field_names[] = $f['field'];
}

?><?='<'?>?php
include_once('../includes/classes/ORM.php');

class <?=$class_name?> extends DBTable {
	/**
	 * Constructor
	 * Arguments : None
	 */
	function __construct() {
		parent::__construct("<?= $table ?>");
	}

	/**
	 * This will create a new <?= $name_single ?> and returns the id of the newly created row.
	 */
	function create($<?=implode(', $', $field_names) ?>) {
<?php
		$field_values = array();
		$validation_rules = "array(\n";
		foreach($fields as $f) {
			$fn = $f['field'];
			$value = '';
			if($f['auto_handler'] === false) {
				
				//Validation
				if(in_array('unique', $f['validation'])) { ?>
		$this->checkDuplicate('<?=$fn?>', $<?=$fn?>);
<?php			}
				
				if(in_array('must',		$f['validation'])) $validation_rules .= "array('name'=>'$fn', 'is'=>'empty', 'error'=>'The $f[title] cannot be empty'),\n";
				if(in_array('email',	$f['validation'])) $validation_rules .= "array('name'=>'$fn', 'is'=>'not_email', 'error'=>'Please provide a valid $f[title]'),\n";
				if(in_array('number',	$f['validation'])) $validation_rules .= "array('name'=>'$fn', 'is'=>'nan', 'error'=>'$f[title] must be a number'),\n";
				
				// Type based diffenences
				if($f['type'] == 'password') {
					if($f['password_encryption_algorithm']) $value = $f['password_encryption_algorithm'] . "(\$$fn . '$f[password_salt]')";
					else $value = "\$$fn";
				
				} else $value = '$' . $f['field'];
			}
			elseif($f['auto_handler'] === 'time_of_insert' || $f['auto_handler'] === 'current_time') $value = "'NOW()'";
			elseif($f['auto_handler'] === 'current_user') $value = '$_SESSION["user_id"]';
		
			if($value) $field_values[$fn] = $value;
		}
		$validation_rules .= ')';
		
		//Generate the validation checking call.
		if($validation_rules != "array(\n)") {
		?>
		$validation_errors = check(<?=$validation_rules?>);
		if($validation_errors) {
			showMessage("Please correct the errors before continuing...", "", "error", $validation_errors);
		}
<?php	} ?>
		
		$this->newRow();
<?php	foreach($field_values as $fn=>$value) { ?>
		$this->field['<?=$fn?>'] = <?=$value?>;
<?php	} ?>
		$id = $this->save();
		return $id;
	}
	
	/**
	 * You can edit an existing <?= $title ?> using this function. The first argument 
	 * 		must be the id of the row to be edited
	 */
	function edit($id, $new_name) {
		if(!$id or !$new_name) return -1;
		
		$this->checkDuplicate($new_name, $id);

		$this->newRow($id);
		$this->field['name'] = $new_name;
		
		return $this->save();
	}
	
	/**
	 * Delete the <?= $title ?> whose id is given
	 * Argument : $id	- The Id of the row to be deleted.
	 */
	function remove($id) {
		if(!$id) return -1;
		global $sql;
	
		$this->where("user_id='$_SESSION[user]'", "id=$id");
		$this->delete();
		if(!$sql->fetchAffectedRows()) return 0;
	}
	
	/**
	 * Checks to make sure that there is no other row with the same value in the specified name.
	 * Example: <?=$class_name?>.checkDuplicate("username", "binnyva", 4);
	 * 			<?=$class_name?>.checkDuplicate("email", "binnyva@gmail.com");
	 */
	function checkDuplicate($field, $value, $not_id=0) {
		//See if an item with that name is already there.
		$others = $this->find(array(
				"select"	=> 'id',
				'where'		=> array("$field='$value'", "id!=$not_id")));
		if($others) {
			showMessage("<?= $name_single ?> '$new_name' already exists!","index.php",'error');
		}
		return false;
	}
}

