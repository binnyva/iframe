<?='<'?>?php
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
	function create(<?php printArgumentList() ?>) {
		$validation_rules = $this->getValidationRules();
<?php
		$field_values = array();
		foreach($fields as $f) {
			if($f['auto_handler'] === false) {
				
				//Validation
				if(in_array('unique', $f['validation'])) { ?>
		$this->checkDuplicate('<?=$f['field']?>', $<?=$f['field']?>);
<?php			}
			}
		}
		?>
		
		$validation_errors = check($validation_rules,2);
		if($validation_errors) {
			$GLOBALS['QUERY']['error'] =  "Please correct the errors before continuing...<br />" . $validation_errors;
			return false;
		}
		
		$this->newRow();
		<?php
		foreach($fields as $f) {
			$fn = $f['field'];
			$value = '';
			// Type based diffenences
			if($f['type'] == 'password') {
				print "		\$validation_rules[] = array('name'=>'$fn', 'is'=>'empty', 'error'=>'Please provide a password');\n";
				if($f['password_encryption_algorithm']) $value = $f['password_encryption_algorithm'] . "(\$$fn . '$f[password_salt]')";
				else $value = "\$$fn";
			
			} else $value = '$' . $f['field'];
			
			if($f['auto_handler']) { //Auto handled values
				if($f['auto_handler'] === 'time_of_insert' || $f['auto_handler'] === 'current_time') $value = "'NOW()'";
				elseif($f['auto_handler'] === 'current_user') $value = '$_SESSION["user_id"]';
				elseif($f['auto_handler'] !== 'none' and $f['auto_handler']) continue; //Some unknown or invalid auto_handler - don't bother with that.
			}
		
		if(!in_array('must',$f['validation']) and $f['auto_handler'] === false) { ?>if($<?=$fn?> !== false) <?php } ?>$this->field['<?=$fn?>'] = <?=$value?>;
		<?php } ?>
		
		return $this->save();
	}
	
	/**
	 * You can edit an existing <?= $title ?> using this function. The first argument 
	 * 		must be the id of the row to be edited
	 */
	function edit($id, <?php printArgumentList(); ?>) {
		if(!$id) return -1;
		
<?php
		$field_values = array();
		foreach($fields as $f) {
			$fn = $f['field'];
			$value = '';
			if($f['auto_handler'] === false) {
				//Uniqe Validation
				if(in_array('unique', $f['validation'])) { ?>
		$this->checkDuplicate('<?=$fn?>', $<?=$fn?>);
<?php			}
			}
		}
		
		?>
		$validation_errors = check($this->getValidationRules(),2);
		if($validation_errors) {
			$GLOBALS['QUERY']['error'] =  "Please correct the errors before continuing...<br />" . $validation_errors;
			return false;
		}
		
		$this->newRow($id);
<?php	foreach($fields as $f) {
			$fn = $f['field'];
			
			if($f['auto_handler'] === 'time_of_update' || $f['auto_handler'] === 'current_time') $value = "'NOW()'";
			elseif($f['auto_handler'] === 'current_user') $value = '$_SESSION["user_id"]';
			elseif($f['auto_handler'] !== 'none' and $f['auto_handler']) continue; //Some unknown auto_handler - don't bother with that.
			else if($f['type'] == 'password') { // Type based diffenences
				if($f['password_encryption_algorithm']) $value = $f['password_encryption_algorithm'] . "(\$$fn . '$f[password_salt]')";
				else $value = "\$$fn";
				print "		if($value) ";
			} else $value = '$' . $f['field'];?>
		<?php if(!in_array('must',$f['validation']) and $f['auto_handler'] === false) { ?>if($<?=$fn?> !== false) <?php } ?>$this->field['<?=$fn?>'] = <?=$value?>;
<?php	} ?>

		return $this->save();
	}
	
	/**
	 * Delete the <?= $title ?> whose id is given
	 * Argument : $id	- The Id of the row to be deleted.
	 */
	function remove($id) {
		if(!$id) return -1;
		<?php
		$user_field = '';
		foreach($fields as $f) {
			if($f['auto_handler'] === 'current_user') $user_field = "\$$f[field]=$_SESSION[user_id], ";
		}
		if($user_field) {
			print '$this->where("' . $user_field. 'id=$id");';
		} else {
			print '$this->newRow($id);';
		}
		?>
		
		$this->delete();
	}
	
	/**
	 * Checks to make sure that there is no other row with the same value in the specified name.
	 * Example: <?=$class_name?>.checkDuplicate("username", "binnyva", 4);
	 * 			<?=$class_name?>.checkDuplicate("email", "binnyva@email.com");
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
	
	function getValidationRules() {
<?php
		$validation_rules = "array(\n";
		foreach($fields as $f) {
			$fn = $f['field'];
			if($f['auto_handler'] === false) {
				if(in_array('must',		$f['validation'])) $validation_rules .= "\t\t\tarray('name'=>'$fn', 'is'=>'empty', 'error'=>'The $f[title] cannot be empty'),\n";
				if(in_array('email',	$f['validation'])) $validation_rules .= "\t\t\tarray('name'=>'$fn', 'is'=>'not_email', 'error'=>'Please provide a valid $f[title]'),\n";
				if(in_array('number',	$f['validation'])) $validation_rules .= "\t\t\tarray('name'=>'$fn', 'is'=>'nan', 'error'=>'$f[title] must be a number'),\n";
				if(in_array('password',	$f['validation'])) $validation_rules .= "\t\t\tarray('name'=>'$fn', 'is'=>'not', 'value_field'=>'confirm_password', 'error'=>'Password and confirmation dont match'),\n";
			}
		}
		$validation_rules .= "\t\t)";
?>		return <?=$validation_rules?>;
	}
}
$GLOBALS['<?=str_replace('$','',$object_name)?>'] = new <?=$class_name?>;

/*
Controllor Constructor Code(JSON):
<?php print json_encode($PARAM); ?>

*/
<?php

function printArgumentList() {
	global $fields;
	
	$length = count($fields);
	for($i=0; $i<$length; $i++) {
		$f = $fields[$i];
		
		if($f['auto_handler'] !== false) continue; // Autohandled stuff will not be sent by the code
		
		print '$'.$f['field'];
		if(!in_array('must', $f['validation'])) { //Make it an optional argument if it don't have the 'must' validation
			print "=false";
		}
		if($i < $length-1) print ', ';
	}
}