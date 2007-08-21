<?='<'?>?php
include_once('../includes/classes/ORM.php');

class <?= $Model['object_name'] ?> extends DBTable {
	/**
	 * Constructor
	 * Arguments : None
	 */
	function __construct() {
		parent::__construct("<?= $Model['table'] ?>");
	}

	/**
	 * This will create a new <?= $Controller['name'] ?> and returns the id of the newly created row.
	 */
	function create($name) {
		if(!$name) return -1;

		if($this->checkDuplicate($name)) {
			showMessage("<?= ucfirst($Controller['name']) ?> '$name' already exists!","index.php",'error');
		}

		$this->newRow();
		$this->field['name'] = $name;
		$this->field['user_id'] = $_SESSION['user'];
		$this->field['created_on'] = 'NOW()';
		$id = $this->save();
		return $id;
	}
	
	/**
	 * You can edit an existing <?= $Controller['name'] ?> using this function. The first argument 
	 * 		must be the id of the row to be edited
	 */
	function edit($id, $new_name) {
		if(!$id or !$new_name) return -1;
		
		if($this->checkDuplicate($new_name, $id)) {
			showMessage("<?= ucfirst($Controller['name']) ?> '$new_name' already exists!","index.php",'error');
		}

		$this->newRow($id);
		$this->field['name'] = $new_name;
		
		return $this->save();
	}
	
	/**
	 * Delete the <?= $Controller['name'] ?> whose id is given
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
	 * Checks to make sure that there is no other row with the same name
	 */
	function checkDuplicate($name, $not_id=0) {
		//See if an item with that name is already there.
		$others = $this->find(array(
				"select"	=> 'id',
				'where'		=> array("name='$name'", "id!=$not_id")));
		if($others) return true;
		return false;
	}
}
$GLOBALS['<?= $Model['object_name'] ?>'] = new <?= $Model['object_name'] ?>();
