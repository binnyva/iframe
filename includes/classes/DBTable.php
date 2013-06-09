<?php
class DBTable {
	////////////////////////////////////// Variables /////////////////////////////////
	private $table_name = '';
	private $query = '';
	private $conditions = array();
	private $select = '*';
	private $limit = false;
	private $offset = 0;
	private $order = '';
	private $group = '';
	private $join_type = 'INNER';
	private $join_table = '';
	private $join_on = '';
	private $query_result_type = 'all';

	public $field = array();
	public $primary_key_field = 'id';
	public $primary_key_value = 0;
	public static $mode = 'p'; ///Mode - p = Production, d = Development and t = Testing
	
	///Constructor
	function __construct($table_name) {
		$this->table_name = $table_name;
	}

	//////////////////////////////////// Public Functions ////////////////////////////
	/**
	 * Creates a query based on the data given to this object. 
	 * Return : $query - The created Query
	 */
	function createQuery() {
		$query = "SELECT {$this->select} FROM {$this->table_name}";
		
		if($this->join_table and $this->join_on) {
			$query .=  " {$this->join_type} JOIN {$this->join_table} ";
			if(strpos($this->join_on, "=") === false)  //If it does not have an '=' sign in the on condition, it just the name of the forign key field instead of the entire condition.
				$query .=  " ON `{$this->table_name}`.`{$this->primary_key_field}`=`{$this->join_table}`.`{$this->join_on}`";
			else
				$query .=  " ON {$this->join_on}";
		}
		
		if(count($this->conditions)) $query .= ' WHERE ' . implode(' AND ',$this->conditions);
		if($this->order) $query .= ' ORDER BY ' . $this->order;
		if($this->group) $query .= ' GROUP BY ' . $this->group;
		if($this->limit) $query .= ' LIMIT ' . $this->offset . ',' . $this->limit;
		
		return $this->query = $query;
	}
	
	/**
	 * Creates a query and executes based on the data given to this object. 
	 * Return : $result - The result of the query.
	 */
	function createExecQuery($type=false) {
		if($type === false) $type = $this->query_result_type;
		
		$this->createQuery();
		return $this->_execQuery($type);
	}
	
	/**
	 * This function is enough to create and execute a query.
	 * 		You can specify any number of argument. If the argument is a number, this function will assume that 
	 *		it is an ID. If it is an array, it will be send to setRequirement() function. If it is a stirng,
	 *		it will be assumed to be a where clause.
	 * Argument : See description
	 * Return : The result of the executed query
	 * Example :$User->find(15);  = SELECT * FROM User WHERE id=15
	 *			$Project->find("user_id=15","name LIKE '%Nexty%'");  = SELECT * FROM Project WHERE user_id=15 AND name LIKE '%Nexty'
	 */
	function find() {
		$arguments = func_get_args();
		
		$ids = array();
		foreach($arguments as $arg) {
			if(is_numeric($arg)) {
				$ids[] = $arg;
			} elseif(is_array($arg)) {
				$this->setRequirement(array('where'=>$arg));
			} elseif(is_string($arg)) {
				$this->where($arg);
			}
		}
		if($ids)
			return $this->findById($ids);
		
		return $this->createExecQuery();
	}
	
	/// Returns the results of all the rows whose IDs are provided
	function findById() {
		$ids = $this->_getArguments(func_get_args());
		
		if(!count($ids)) return false;
		
		if(count($ids) == 1) { //Just one ID
			$this->where("`{$this->primary_key_field}`='$ids[0]'");
		} else { //Multiple IDs - use IN to get all the data
			$this->where("`{$this->primary_key_field}` IN (" . implode(",", $ids). ")");
		}

		$data = $this->createExecQuery('assoc');
		
		if(count($ids) == 1) {
			$this->field = $data;
			$this->primary_key_value = $ids[0];
		}

		return $data;
	}
	
	/// Write the changes to the DB. If its a new row, an insert will happen. If it is an existing row, an update
	function save($id = 0) {
		global $sql;
		if($id) $this->primary_key_value = $id;

		if(!count($this->field)) return false;
		$return_value = -1;
		
		if($this->primary_key_value) { //If we have the private key, it is an existing row - so do an update
			$this->query = "UPDATE `{$this->table_name}` SET ";
			$update_array = array();
			foreach($this->field as $field_name => $field_value) {
				if ($sql->isKeyword($field_value)) { //If the is values has a special meaning - like NOW() give it special consideration
					$update_array[] = "`$field_name`=$field_value";
				} else {
					$update_array[] = "`$field_name`=" . $this->_escape($field_value);
				}
			}
			$this->query .= implode(', ',$update_array);
			
			$this->where("`{$this->primary_key_field}`={$this->primary_key_value}");
			$this->query .= ' WHERE ' . implode(' AND ',$this->conditions);

 			$this->_execQuery('exec');
 			$return_value = $sql->fetchAffectedRows();

		} else { //New row - do an insert
			$field_names = array_keys($this->field);
			$field_values = array_values($this->field);

			for($i=0; $i<count($field_values); $i++) {
				if(!$sql->isKeyword($field_values[$i])) {//Quote the value if it is not a Function call
					$field_values[$i] = $this->_escape($field_values[$i]);
				}
			}
			
			$this->query = "INSERT INTO `{$this->table_name}` (`" . implode('`,`', $field_names) . '`) '
					. ' VALUES (' .implode(",", $field_values) . ')';

			$this->_execQuery('exec');
			$return_value = $sql->fetchInsertId();
		}
		$this->field = array(); //Reset the data after the save
		
		return $return_value;
	}
	
	/**
	 * Execute a DELETE statement. If the ID of the deleted row is not specified as the argument, 
	 *		the function will use just the where clause. Be careful - if the where clauses are not present, 
	 *		kiss you table goodbye!
	 * Arguments : An ID or a list of IDs - Optional
	 * Returns : Affected Row count
	 * Example : $user->delete(5,4);
	 */
	function delete() {
		global $sql;

		$this->query = "DELETE FROM `{$this->table_name}` ";
		$ids = $this->_getArguments(func_get_args());
		
		if(count($ids)) {
			if(count($ids) == 1) {
				$this->query .= " WHERE `{$this->primary_key_field}` = " . $ids[0];
			} else {
				$this->query .= " WHERE `{$this->primary_key_field}` IN (" . implode(",", $ids). ")";
			}
			if(count($this->conditions)) $this->query .= ' AND ' . implode(' AND ',$this->conditions);

		} else { //Remove muliptle rows at once
			if($this->primary_key_value) $this->query .= " WHERE `{$this->primary_key_field}` = {$this->primary_key_value}"; // If the user made an newRow() call before the delete.
			if(count($this->conditions)) $this->query .= ' WHERE ' . implode(' AND ',$this->conditions);
		}
		
		$this->_execQuery('exec');
		return $sql->fetchAffectedRows();
	}

	///Resets all the data of the previous query
	function newRow($id = 0) {
		$this->query = '';
		$this->field = array();
		$this->primary_key_value = $id;
		$this->conditions = array();
		$this->select = '*';
		$this->limit = false;
		$this->offset = 0;
		$this->order = '';
		$this->group = '';
		$this->query_result_type = 'all';
		
		return $this;
	}
	
	/** Sets the data for this save
	 * Example: $Task->newRow(14)->set(array('name'=>'Implement Chainable interfaces in ORM', 'status'=>'done'))->save();
	 */
	function set($field_data) {
		$this->field = $field_data;
		return $this;
	}
	
	function setRequirement($arg) {
		//The where clauses
		if(isset($arg['conditions']))	$this->where($arg['conditions']);
		if(isset($arg['where']))		$this->where($arg['where']);

		// LIMIT offset, limit
		if(isset($arg['limit'])) $this->limit = $arg['limit'];

		// LIMIT offset, limit
		if(isset($arg['offset'])) $this->offset = $arg['offset'];

		// ORDER BY order
		if(isset($arg['order'])) $this->order = $arg['order'];
		
		// GROUP BY group
		if(isset($arg['group'])) $this->group = $arg['group'];
		
		// SELECT select
		if(isset($arg['select'])) $this->select($arg['select']);
		
		if(isset($arg['result_type'])) $this->query_result_type = $arg['result_type'];
		
		//If its just array with none of our 'special' keys - it is consided to be an array of where clauses
		if(!isset($arg['conditions']) and !isset($arg['where']) and !isset($arg['limit']) 
				and !isset($arg['offset']) and !isset($arg['order']) and !isset($arg['group']) 
				and !isset($arg['select']) and !isset($arg['result_type'])) {
			$this->where($arg);
		}
	}

	//////////////////////////////////////// Chainable Functions ////////////////////////////////////
	/**
	 * Pass which all fields must be selected into this function
	 * Example: $User->select('id', 'name')->get();
	 * 		OR  $User->select(array('id', 'name'))->get();
	 * 		OR	 $User->select('id,name')->get();
	 */
	function select() {
		$arguments = $this->_addTableName(func_get_args());
		
		if($arguments) {
			$this->select = $arguments;
		}
		return $this;
	}
	
	/**
	 * Specify the WHERE statements here. 
	 * Arguments : A list of all the conditions
	 * Example: $User->where(array("name='Binny'","age=23"));
	 *			$User->where("name='Binny'","age=23")->get();
	 */
	function where() {
		global $sql;
		$conditions = $this->_getArguments(func_get_args()); // Don't call _addTableName for this - it may mess things up
		
		foreach($conditions as $key => $cond) {
			if(is_string($key)) {
				if(!in_array($cond,$this->conditions)) $this->conditions[] = "`$key`='".$sql->escape($cond)."'";
			
			} else if(is_string($cond)) {
				if(!in_array($cond,$this->conditions)) $this->conditions[] = $cond;
			}
		}
		return $this;
	}
	
	/**
	 * Inserts a ORDER BY clause into the query.
	 * Argument: A list of fields that must be used to sort the data.
	 * Example : $Comic->sort('added_on DESC','name')->get('all');
	 */
	function sort() {
		$arguments = $this->_addTableName(func_get_args());
		if($arguments) $this->order = $arguments;
		
		return $this;
	}
	
	/**
	 * You can use this function to join tables togetget - inserting the JOIN clause.
	 * Example: $User->join('Article', 'Article.user_id=User.id') ...
	 */
	function join($table, $on, $type = '') {
		$this->join_table = $table;
		$this->join_on = $on;
		if(!$type) $this->join_type = $type;
		
		return $this;
	}
	
	/**
	 * Inserts a GROUP BY clause into the query.
	 * Argument: A list of fields that must be used to group the data.
	 * Example : $Comic->group('category_id')->get('all');
	 */
	function group() {
		$arguments = $this->_addTableName(func_get_args());
		if($arguments) $this->order = $arguments;
		
		return $this;
	}
	
	/** 
	 * Inserts a LIMIT clause.
	 * Arguments: $limit - The number of results to be returned
	 * 			  $offset - The number of the row to start the fetch.
	 * Example: $User->select('name')->where('age=24')->limit(1)->get('one');
	 */
	function limit($limit, $offset=0) {
		$this->limit = $limit;
		$this->offset = $offset;
		return $this;
	}
	
	/**
	 * This function returns the data after all the chaining.
	 * Argument: $type - Return type - can be 'all', 'one', 'byid', 'assoc'. Defaults to 'all'.
	 * Example : $Comic->select('name')->get('one');
	 */
	function get($type=false) {
		return $this->createExecQuery($type);
	}
	
	/**
	 * Returns the generated SQL Query.
	 */
	function getSqlQuery() {
		$this->createQuery();
		return $this->query;
	}

	//////////////////////////////////////////// The Privates /////////////////////////////////////
	private function _getArguments($id_list) {
		$arguments = $id_list;
		if(count($arguments) == 1 and is_array($arguments[0])) { //If the first argument is the list(array) of IDs
			$arguments = $arguments[0];
		}
		return $arguments;
	}
	
	/// The SQL is executed only here.
	private function _execQuery($return_type) {
		global $sql;
		$result = array();
		
		if(DBTable::$mode == 't') { //Just testing, fools!
			print $this->query . '<br />';

		} else {
			$result = array();
			if($return_type == 'assoc') {
				$result = $sql->getAssoc($this->query);
			} elseif($return_type == 'all') {
				$result = $sql->getAll($this->query);
			} elseif($return_type == 'one') {
				$result = $sql->getOne($this->query);
			} elseif($return_type == 'col') {
				$result = $sql->getCol($this->query);
			} elseif($return_type == 'byid') {
				$result = $sql->getById($this->query);
			} else { //exec
				$sql->getSql($this->query);
			}
		}
		$this->newRow();
		
		return $result;
	}
	
	/// This function adds the name of the table to the beginning of each field name. For eg, 'name' will become "`User`.`name`"
	private function _addTableName($arguments) {
		$arguments = $this->_getArguments($arguments);
		
		$argument_count = count($arguments);
		if($argument_count == 1 and strpos($arguments[0], ',') !== false) { //Someone may call this function like this: $User->select('id, name')->get(); This code handles it.
			$arguments = explode(',', $arguments[0]);
			$argument_count = count($arguments);
		}
		
		for($i=0; $i<$argument_count; $i++) {
			if(strpos($arguments[$i], '.') === false //If the table name is not specified in the field name, add the table name. Otherwise there will be problems when joining tables.
					and strpos($arguments[$i], '(') === false) { //Function calls must not be modified.
				$arguments[$i] = "`{$this->table_name}`.{$arguments[$i]}"; //Adding the `s around the field name may mess stuff like "sort('name DESC')"
			}
			else $arguments[$i] = $arguments[$i];
		}

		return implode(',',$arguments);;
	}
	
	private function _escape($string) {
		global $sql;
		return "'" . $sql->escape($string) . "'";
	}
}

/*
 * :TODO:
 * Support for OR in WHERE conditions
 */
