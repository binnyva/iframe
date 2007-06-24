<?php
class DBTable {
	private $table_name = '';
	private $query = '';
	private $conditions = array();
	private $select = '*';
	private $limit = false;
	private $offset = 0;
	private $order = '';
	private $group = '';

	public $field = array();
	public $primary_key_field = 'id';
	public $primary_key_value = 0;
	public static $mode = 'p'; ///Mode - p = Production, d = Development and t = Testing
	
	///Constructor
	function __construct($table_name) {
		$this->table_name = $table_name;
	}

	function createQuery() {
		$query = "SELECT {$this->select} FROM {$this->table_name}";
		
		if(count($this->conditions)) $query .= ' WHERE ' . implode(' AND ',$this->conditions);
		if($this->order) $query .= ' ORDER BY ' . $this->order;
		if($this->group) $query .= ' GROUP BY ' . $this->group;
		if($this->limit) $query .= ' LIMIT ' . $this->offset . ',' . $this->limit;
		
		$this->query = $query;
	}
	
	function createExecQuery($type) {
		$this->createQuery();
		return $this->execQuery($type);
	}
	
	function find() {
		$arguments = func_get_args();
		
		$ids = array();
		foreach($arguments as $arg) {
			if(is_numeric($arg)) {
				$ids[] = $arg;
			} elseif(is_array($arg)) {
				$this->setRequirement($arg);
			}
		}
		if($ids)
			return $this->findByIds($ids);
		
		return $this->createExecQuery('all');
	}
	
	/** Specify the WHERE statements here
	 * Example: $User->where(array("name='Binny'","age=23"));
	 */
	function where() {
		$conditions = func_get_args();
		foreach($conditions as $cond) {
			if(is_string($cond))
				if(!in_array($cond,$this->conditions)) $this->conditions[] = $cond;
		}
	}
	
	/// Returns the results of all the rows whose IDs are provided
	function findByIds() {
		$ids = $this->_getIds(func_get_args());
		
		if(count($ids) == 1) { //Just one id
			return $this->findById($ids[0]);
		}

		// Get the result for each ID
		foreach($arguments as $id) {
			$returns[] = $this->findById($id);
		}
		return $returns;
	}

	/// Return the row whose id is provided
	function findById($id) {
		$this->where("`{$this->primary_key_field}`='$id'");
		
		$data = $this->createExecQuery('assoc');
		$this->field = $data;
		$this->primary_key_value = $id;

		return $data;
	}
	
	/// Pass which all fields must be selected into this function
	function select() {
		$arguments = func_get_args();
		
		$select = '*';
		if(count($arguments)) {
			if(count($arguments) == 1 and is_array($arguments[0])) { //If the first argument is the list of fields
				$arguments = $arguments[0];
			}
			
			$select = '`' . implode('`,`',$arguments) . '`';
		}
		$this->select = $select;
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
				if ($this->_isKeyword($field_value)) { //If the is values has a special meaning - like NOW() give it special consideration
					$update_array[] = "`$field_name`=$field_value";
				} else {
					$update_array[] = "`$field_name`=" . $this->escape($field_value);
				}
			}
			$this->query .= implode(', ',$update_array);
			
			$this->where("`{$this->primary_key_field}`={$this->primary_key_value}");
			$this->query .= ' WHERE ' . implode(' AND ',$this->conditions);

			$this->execQuery('exec');
			$return_value = $sql->fetchAffectedRows();

		} else { //New row - do an insert
			$field_names = array_keys($this->field);
			$field_values = array_values($this->field);

			for($i=0; $i<count($field_values); $i++) {
				if(!$this->_isKeyword($field_values[$i])) {//Quote the value if it is not a Function call
					$field_values[$i] = $this->escape($field_values[$i]);
				}
			}
			
			$this->query = "INSERT INTO `{$this->table_name}` (`" . implode('`,`', $field_names) . '`) '
					. ' VALUES (' .implode(",", $field_values) . ')';

			$this->execQuery('exec');
			$return_value = $sql->fetchInsertId();
		}
		$this->field = array(); //Reset the data after the save
		
		return $return_value;
	}
	
	function delete() {
		global $sql;

		$this->query = "DELETE FROM `{$this->table_name}` ";
		$ids = $this->_getIds(func_get_args());
		if(count($ids)) {
			if(count($ids) == 1) {
				$this->query .= " WHERE `{$this->primary_key_field}` = " . $ids[0];
			} else {
				$this->query .= " WHERE `{$this->primary_key_field}` IN (" . implode(",", $ids). ")";
			}

		} else { //Remove muliptle rows at once
			if(count($this->conditions)) $this->query .= ' WHERE ' . implode(' AND ',$this->conditions);
		}
		$this->execQuery('exec');
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
	}


	//////////////////////////////////////////// The Privates /////////////////////////////////////
	private function _getIds($id_list) {
		$arguments = $id_list;
		if(count($arguments) == 1 and is_array($arguments[0])) { //If the first argument is the list(array) of IDs
			$arguments = $arguments[0];
		}
		return $arguments;
	}

	private function _isKeyword($value) {
		$value = strtoupper($value);
		if(preg_match('/^\s*(\w+)\((.*)\)\s*$/',$value,$match)) {
			$function_name = $match[1];

			//A list of all mysql functions - taken from http://dev.mysql.com/doc/refman/4.1/en/func-op-summary-ref.html
			$mysql_functions = array('ABS','ACOS','ADDDATE','ADDTIME','AES_DECRYPT','AES_ENCRYPT','ASCII','ASIN',
				'ATAN2,','ATAN','AVG','BENCHMARK','BIN','BIT_AND','BIT_COUNT','BIT_LENGTH','BIT_OR','BIT_XOR','CAST',
				'CEILING,','CHAR_LENGTH','CHAR','CHARACTER_LENGTH','CHARSET','COALESCE','COERCIBILITY','COLLATION',
				'COMPRESS','CONCAT_WS','CONCAT','CONNECTION_ID','CONV','CONVERT_TZ','COS','COT','COUNT','COUNT',
				'CRC32','CURDATE','CURRENT_DATE,','CURRENT_TIME,','CURRENT_TIMESTAMP,','CURRENT_USER,','CURTIME',
				'DATABASE','DATE_ADD','DATE_FORMAT','DATE_SUB','DATE','DATEDIFF','DAY','DAYNAME','DAYOFMONTH',
				'DAYOFWEEK','DAYOFYEAR','DECODE','DEFAULT','DEGREES','DES_DECRYPT','DES_ENCRYPT','DIV','ELT',
				'ENCODE','ENCRYPT','EXP','EXPORT_SET','FIELD','FIND_IN_SET','FLOOR','FORMAT','FOUND_ROWS',
				'FROM_DAYS','FROM_UNIXTIME','GET_FORMAT','GET_LOCK','GREATEST','GROUP_CONCAT','HEX','HOUR','IF',
				'IFNULL','INET_ATON','INET_NTOA','INSERT','INSTR','INTERVAL','IS_FREE_LOCK','IS_USED_LOCK','ISNULL',
				'LAST_DAY','LAST_INSERT_ID','LCASE','LEAST','LEFT','LENGTH','LN','LOAD_FILE','LOCALTIME,',
				'LOCALTIMESTAMP,','LOCATE','LOG10','LOG2','LOG','LOWER','LPAD','LTRIM','MAKE_SET','MAKEDATE',
				'MAKETIME','MASTER_POS_WAIT','MAX','MD5','MICROSECOND','MID','MIN','MINUTE','MOD','MONTH','MONTHNAME',
				'NOW','NULLIF','OCT','OCTET_LENGTH','OLD_PASSWORD','ORD','PASSWORD','PERIOD_ADD','PERIOD_DIFF','PI',
				'POSITION','POW,','QUARTER','QUOTE','RADIANS','RAND','RELEASE_LOCK','REPEAT','REPLACE','REVERSE',
				'RIGHT','ROUND','RPAD','RTRIM','SEC_TO_TIME','SECOND','SESSION_USER','SHA1,','SIGN','SIN','SOUNDEX',
				'SOUNDS','SPACE','SQRT','STD,','STR_TO_DATE','STRCMP','SUBDATE','SUBSTRING_INDEX','SUBSTRING,',
				'SUBTIME','SUM','SYSDATE','SYSTEM_USER','TAN','TIME_FORMAT','TIME_TO_SEC','TIME','TIMEDIFF',
				'TIMESTAMP','TO_DAYS','TRIM','TRUNCATE','UCASE','UNCOMPRESS','UNCOMPRESSED_LENGTH','UNHEX',
				'UNIX_TIMESTAMP','UPPER','USER','UTC_DATE','UTC_TIME','UTC_TIMESTAMP','UUID','VALUES','VARIANCE',
				'WEEK','WEEKDAY','WEEKOFYEAR','YEAR','YEARWEEK');

			if(in_array($function_name, $mysql_functions)) { //The function is a valid mysql keyword
				return true;
			}
		}
		return false;
	}
	
	
	/// The SQL is executed only here.
	private function execQuery($return_type) {
		global $sql;
		$result = array();
		
		if(DBTable::$mode == 't') { //Just testing, fools!
			print $this->query . '<br />';

		} else {
			$resource = $sql->getSql($this->query);
			
			if($resource and $resource !== true) {
				$rows = $sql->fetchNumRows($resource);
				if($rows) {
					if($rows == 1) { //Just 1 row - get the result as an associate array
						$result = $sql->getAssoc($resource);
						//Get the ID of this row
						if(isset($results[$this->primary_key_field])) $this->primary_key_value = $id;
						
					} else {
						$result = $sql->getAll($resource);
					}
				}
			} elseif(!$this->primary_key_value) {
				$this->primary_key_value = $sql->fetchInsertId();
			}
		}
		$this->newRow();
		
		return $result;
	}
	
	private function escape($string) {
		return "'" . mysql_real_escape_string($string) . "'";
	}
}
