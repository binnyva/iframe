<?php
$html = new HTML;
 
class Crud {
	public $title;						// Title of the page	Eg: User
	public $title_plural;				// The plural of the title. Eg: Users
	public $table;						// The name of the table. Eg: User
	public $primary_key = 'id';			// The name of the primary key of the table Eg: id. You can use the setPrimaryKey() to set this value.
	public $status_field;				// The field that contains the status bit. Use the setStatusField() to set the value for this variable.
	public $sort_field;					// The field that contains the sort order. Use the setSortField() to set the value for this variable.
	public $fields = [];			// Holds all the data of the fields in this page. Do not edit by hand if you don't know what you are doing. Use addField() to edit this.
	public $action = 'list';			// The action of current page - could be 'list', 'add', 'edit', 'delete', 'add_save', 'edit_save', 'activate', 'deactivate', 'toggle_status'.
	
	public $items_per_page = 20;		// Number of items per page - this will be sent into the SqlPager class.
	public $urls = array(				// URLs of some main areas.
		'main'				=> '',					// The listing URL - just the file and nothing else.
		'add'				=> '?action=add',		// The add page URL
		'edit'				=> '?action=edit',		// The edit page URL
		'delete'			=> '?action=delte',		// The delete page URL
		
		'js_folder'			=> 'crud/',			// The Javascript folder. This folder is inside the js/ folder. So if this is crud/, that means the js files are in /js/crud/
		'css_folder'		=> 'crud/',			// The folder that holds CSS files.
		'image_folder'		=> 'crud/', 		// The folder in which the images are kept - Will be dynamially set in the consturctor.
	);
	
	public $listing_query	= '';		// The query use to create the listing page. Use setListingQuery() to set this query. That will make sure all the extra parts(sorting, paging) etc. stays intact.
	public $listing_fields	= [];	// An array of list of fields that should be shown in the listing page - use setListingFields() to set this value.
	public $form_fields		= [];	// A list of fields to be shown in the add/edit pages. Use setFormFields() to set this.
	public $search_fields	= [];	// The fields that should allow searching. Use setSearchFields() to edit this.
		
	public $current_page_data;			// An associate array that holds all the data of the current page. This is for the listing action.
	public $pager;						// The pager for the listing page. Holds the object of the Pager class.
	
	public $folder = array(				// Some important folders.
		'uploads'			=> 'user_uploads',	// The folder in which the uploaded stuff are stored.
	);
	
	public $allow = array(				// Some config settings - mostly boolean stuff
		'sorting' 			=> true,	// Show the sorting buttons?
		'bulk_operations'	=> true,	// Allow bulk operations like bulk/activate/inactivate delete using checkboxs
		'searching'			=> true,	// Show the search form.
		'status_change'		=> true,	// Allow the status changes. If false, the single click status toggle will be disable, as will the bulk status change thingy.
		'header'			=> true,	// Show header in the listing.
		'save_and_edit_form_button'	=> true,	//Shows the 'Save and Continue Editing' button in the edit form.
		'save_and_new_form_button'	=> true,	//Shows the 'Save and Show New Form' button in the edit form.
		
		'add'				=> true,	// Allow the user to add new rows.
		'delete'			=> true,	// Lets the user delete stuff.
		'edit'				=> true,	// Editing existing row is allowed.
	);
	
	public $save_states		= array('iframe_crud_search','iframe_crud_search_in','sp_page','sp_items_per_page','sortasc','sortdesc');
	public $search_field_types = array('varchar', 'link'); // The type of fields that should show up in the search option.

	private $data_type_field_type_map = array(	// What kind of data type maps to what kind of html field.
		'virtual'			=> 'text',
		'varchar'			=> 'text',
		'text'				=> 'textarea',
		'mediumtext'		=> 'textarea',
		'int'				=> 'text',
		'tinyint'			=> 'text',
		'bigint'			=> 'text',
		'float'				=> 'text',
		'double'			=> 'text',
		'datetime'			=> 'datetime',
		'timestamp'			=> 'datetime',
		'date'				=> 'date',
		'enum'				=> 'select',
		'manytomany'		=> 'select',
		'time'				=> 'text',
		'bit'				=> 'checkbox',
		'file'				=> 'file',
	);
	
	public $success = '';				// A success message - if any.
	public $error = '';					// Error message holder.
	
	public $code = array(
		'top'	=> '',					// Prints this after showTop() and before printAction()
		'bottom'=> '',					// Prints this after printAction() and before showEnd()
		'before_content'	=> '',		// Show this before the content is shown
		'multi_select_choice'	=> '',	// In the Delete, Activate, Deactivate part - below all the rows.
	);
	
	
	///////////////////////////////////////// Configuration Function ////////////////////////////////////
	function __construct($table, $title='', $primary_key='id', $guess=true) {
		global $config, $template;
		$this->table = $table;
		
		if($title) $this->title = $title;
		else $this->title = format($table);
				
		// If the title ends with 's' or 'es', set that as the plural.
		if(preg_match('/e?s$/', $this->title)) {
			$this->title_plural = $this->title;
			$this->title = preg_replace('/e?s$/', '', $this->title);
		} else {
			if(substr($this->title, -1, 1) == 'y') $this->title_plural = preg_replace('/i?y$/', 'ies', $this->title);
			else $this->title_plural = $this->title . 's';
		}
		
		//Set some member variables
		if(!empty($_REQUEST['action'])) $this->action = $_REQUEST['action'];

		if(!empty($config['common_library_url'])) $this->urls['image_folder'] = joinPath($config['common_library_url'], 'images/', $this->urls['image_folder']);
		else $this->urls['image_folder'] = joinPath($config['site_url'], 'images/', $this->urls['image_folder']);
		
		$this->setPrimaryKey($primary_key);
		$this->setUrl();
		
		if($guess) $this->guess();
	}
	
	/// Guesses all the fields based on the the Database structure.
	function guess() {
		$all_fields_info = $this->execQuery("SHOW FIELDS IN {$this->table}", "all");
		foreach($all_fields_info as $field_info) {
			extract($field_info);
			if(!$Field) continue;

			$field_type = false;
			$value_type = false;
			$validation = [];
			$data = [];
			$field_title = format($Field);
			
			if($Key == 'PRI') $this->setPrimaryKey($Field);
			else {
				$data_type = preg_replace("/^([a-z]+).*$/", "$1", $Type);
				
				switch($data_type) {
					case 'enum':
						$vals = preg_replace('/.*\((.+)\)/', "$1", $Type);
						$vals = str_replace("'", "", $vals);
						$all_vals = explode(',', $vals);
						
						foreach($all_vals as $v) {
							$data[$v] = format($v);
						}

						if($Field == 'status') {
							$this->setStatusField($Field);
							$field_type = 'checkbox';
							$value_type = 'status';
							$data_keys = array_keys($data);
							$data = array_shift($data_keys); // First element in the enum list is the default value.
							
						} else if($Type == "enum('1','0')" or $Type == "enum('0','1')") {
							$field_type = 'checkbox';
							if($Default == '1' or $Default == '0') {
								$data = $Default;
							} else {
								$data_keys = array_keys($data);
								$data = array_shift($data_keys); // First element in the enum list is the default value.
							}
						}
						break;
					case 'int':
						// If it is a foreign key...
						if(preg_match('/^(.+)_id$/', $Field, $matches)) {
							$data_type = 'enum';
							$validation = [];
							$reference_table = str_replace(array('parent_', 'parent'), '', $matches[1]);
							
							if(ctype_upper($this->table[0])) $reference_table = ucfirst($reference_table); // In my designs, I tend to upper case the first char of the table. If the current table has first char uppercased, try doing the same to the reference table.
							if(!$reference_table) $reference_table = $this->table;
							$field_title = ucfirst(str_replace('_',' ', $reference_table));
							
							// Check if the table exists...
							$reference_table_exists = $this->execQuery("SHOW TABLES LIKE '{$reference_table}'", 'one');
							if($reference_table_exists) {
								$data = $this->execQuery("SELECT id,name FROM `{$reference_table}`", "byid");
								$data['0'] = 'None';
							} else {
								$data = array("No Data");
							}
						}

						if($Field == 'sort' or $Field == 'order' or $Field == 'sortorder' or $Field == 'sort_order' or $Field == 'sort_by') {
							$field_type = 'input';
							$value_type = 'sort';
							$this->setSortField($Field);
						}
						break;
					case 'varchar':
						$length = preg_replace('/.*\((.+)\).*/', "$1", $Type);
						if($length > 250) $field_type = 'textarea';
						$validation['length<'] = $length;
				}
				
				$this->addField($Field, $field_title, $data_type, $validation, $data, $field_type, $value_type);
			}
		}
		// Some pre render stuff.
		$this->setListingFields();
		$this->setFormFields();
		$this->setHeaders();
	}
	
	/// Sets the given field name as the primary key of the current table.
	function setPrimaryKey($field) {
		$this->primary_key = $field;
	}
	
	/// Sets the status field for this table
	function setStatusField($field_name) {
		$this->status_field = $field_name;
	}

	/// Sets the sort order field for this table
	function setSortField($field_name) {
		$this->sort_field = $field_name;
	}
	
	/**
	 * Add a field to the field list - if a field with the same name is present in the list, overwrite it.
	 * Example:
 	 * Makes a list field - with a select when edited...
	 * $crud->addField("donation_status", 'Donation Status', 'enum', [], array(
	 *			'TO_BE_APPROVED_BY_POC' => 'Not Approved',
	 *			'DEPOSIT COMPLETE'		=> 'Approved'
	 * 		), 'select');
	 *
	 *
	 * Arguments: $field - the name of the field. Eg: url
	 * 			  $name - The field title. Eg: URL
	 * 			  $type - Datatype for that field . Eg: varchar
	 * 			  $validation - an array that specifies all the necessary validations. Eg: array('empty','url');
	 * 			  $data - some preset data. Useful for setting hidden variables and Dropdown boxes.
	 * 			  $field_type - what kind of HTML field.
	 * 			  $value_type - what kind of data(email, name, url, etc).
	 *			  $extra_info - More info if needed.
	 */
	function addField($field, $name=false, $type='varchar', $validation=[], $data=[], $field_type=false, $value_type=false, $extra_info=[]) {
		if($name === false) $name = format($field);
		
		if(!$field_type or !$value_type or !$validation) {
			$info = $this->_guessFieldTypes($field, $name, $type, $validation);
			if(!$field_type) $field_type = $info['field_type'];
			if(!$value_type) $value_type = $info['value_type'];
			if(!$validation) $validation = $info['validation'];
		}
		
		$field_info = array(
			'field'		 =>	$field,
			'name'		 =>	$name,
			'type'		 =>	$type,
			'field_type' => $field_type,
			'value_type' => $value_type,
			'validation' => $validation,
			'data'		 =>	$data,
			'extra_info' => $extra_info,
		);

		$this->fields[$field] = $field_info;
	}
	
	/**
	 * Add a forign field's data as a dropdown list.
	 * Example: 
	 * $crud->addListDataField("user_id", "User"); // Default relation. This actually needn't be specified - Crud can guess this.
	 * $crud->addListDataField("donor_id", "donours", "Donor", "", array('fields' => 'id,first_name')); // When you need custom fields rather than default
	 * Arguments: $field - the name of the field. Eg: url
	 *            $name - the title of the field. 'URL'
	 *            $table - the table wth the date that should be used n the dropdown.
	 *            $where - the conditions for the data.
	 */
	function addListDataField($field, $table, $name=false, $where='', $options=[]) {
		if(!empty($where)) $where = " WHERE $where";

		$fields = i($options, 'fields', 'id,name');
		$values = $this->execQuery("SELECT $fields FROM {$table} $where", "byid");
		if(isset($options['none'])) {
			$values[0] = $options['none'][0];
		}

		$this->addField($field, $name, 'enum', [], $values);
	}

	
	/**
	 * Add a many to many relation field.
	 * Example: (Fairly complicated...)
	 * $crud->addManyToManyField('selected_users', 'HR_UserSelect', 
	 *			array(
	 *				array(
	 *					'table'		=> 'User',
	 *					'forign_key'=> 'user_id',
	 *					'select'	=> 'id,name',
	 *					'where'		=> "city_id=$city_id AND user_type='volunteer' AND status='1'",
	 *				),
	 *				array(
	 *					'table'		=> 'HR_Engagement',
	 *					'forign_key'=> 'item_id',
	 *				),
	 *				'where' => array('item_type'=>"engagement"),
	 *			), 'Selected Users');
	 */
	function addManyToManyField($field, $reference_table, $field_list, $title='', $data=[]) {
		if(!$data) {
			$where = i($field_list[0], 'where');
			if($where) $where = ' WHERE ' . $where;

			$data = $this->execQuery("SELECT ".i($field_list[0], 'select', 'id,name')." FROM `".$field_list[0]['table']."` $where", "byid");
		}
	
		$this->addField($field, $title, 'manytomany', [], $data, 'select', false, 
				array('reference_table' => $reference_table, 'field_list' => $field_list));
	}

	/**
	 * Add a field that can only be seen when the data is being listed(list action). This field don't have to be in the database. 
	 *	It can also be an SQL statement with auto replaces with fields in the current row.
	 * Example: 
	 * $admin->addListingField('User Posts',array('html'=>'"<a href=\'$row[url]\'>View All Post of this User</a>"')); // Show HTML in the field
	 * $admin->addListingField('User Posts',array('sql'=>'SELECT COUNT(U.id) FROM User WHERE city_id='%city_id%'));   // Get data from the DB and show it.
	 */
	function addListingField($title, $data) {
		// if(($this->action == 'list') or ($this->action == 'add_save') or ($this->action == 'edit_save')) { // These where here originally. Removed it because I don't know why I put them there.
			if(is_array($data)) $data_array = $data;
			else $data_array = array('html'=>$data);

			$this->addField(unformat($title), $title, 'virtual', [], $data_array);
		// }
		$this->setListingFields();
	}
	
		
	/**
	 * Set the rules to validate this field.
	 * Arguments:	$field - the name of the field to which these rules must be attached to.
	 *				$validation_rules - An array of rules that specifies the validation for that field. This will be passed into the check() for both PHP and JS.
	 * Example: $admin->setValidation('name', array('must'=>true, 'length<'=>100));
	 * 			$admin->setValidation('email', array('must'=>true, 'email'=>true));
	 */
	function setValidation($field, $validation_rules) {
		if(isset($this->fields[$field])) $this->fields[$field]['validation'] = $validation_rules;
	}
	
	
	/**
	 * Specify all the fields that could be searched in.
	 * Example: $admin->setSearchFields('name','url');
	 */
	function setSearchFields() {
		$given_search_fields = $this->_getArguments(func_get_args());
		$search_fields = [];
		
		// If fields are not specified, include all varchar fields
		if(!$given_search_fields) $given_search_fields = array_keys($this->fields);
		
		foreach($given_search_fields as $name) {
			if(isset($this->fields[$name]['type']))
				if(in_array($this->fields[$name]['type'], $this->search_field_types))
					$search_fields[$name] = $this->fields[$name]['name'];
		}

		$this->search_fields = $search_fields;
	}
	
	/**
	 * The given arguments are set as the fields that should shown in the listing page.
	 * Example: $admin->setListingFields("username", "name", "url");
	 */
	function setListingFields() {
		$listing_fields = $this->_getArguments(func_get_args());
		
		// If fields are not specified, include everything.
		if(!$listing_fields) {
			foreach($this->fields as $name=>$f) $listing_fields[] = $name;
		} else {
			foreach ($listing_fields as $field_name) {
				if(!isset($this->fields[$field_name])) { // If the given field is not there in the List of existing fields, initiate that field.
					$this->fields[$field_name] = array(
						'field'			=> $field_name,
						'name'			=> format($field_name),
						'type'			=> 'varchar',
						'field_type'	=> 'varchar',
						'value_type'	=> 'text',
						'validation'	=> [],
						'data'			=> [],
						'extra_info' 	=> [],
					);
				}
			}			
		}
		$this->listing_fields = $listing_fields;
		
		$this->setSearchFields($listing_fields);
	}
	
	/**
	 * The field provided as the arguments will be shown in the add/edit forms..
	 * Example: $admin->setFormFields('username','password','email','url','added_on');
	 */
	function setFormFields() {
		$form_fields = $this->_getArguments(func_get_args());
		
		// If fields are not specified, include everything.
		if(!$form_fields) {
			foreach($this->fields as $name=>$f) $form_fields[] = $name;
		}
		$this->form_fields = $form_fields;
	}
	
	/**
	 * Set the URL of the page. This function also finds and sets the add/edit/delete urls based on the given URL. If an empty URL is given, the URL of the current page is taken.
	 * Example: $admin->setUrl('http://example.com/admin/users.php');
	 */
	function setUrl($url = '') {
		if(!$url) {
			// Get the current URL.
			if(isset($_SERVER['REQUEST_URI'])) {
				$url = $_SERVER['REQUEST_URI'];
			} else {
				$url = $_SERVER["SCRIPT_NAME"];
				if($_SERVER["QUERY_STRING"]) $url .= '?' . $_SERVER["QUERY_STRING"]; // To make sure this works in IIS too.
			}
			
			// Remove some unwanted stuff in the URL
			$items_to_remove = array('id','select_row[]','action','iframe_crud_search','iframe_crud_search_in', 'sp_page','sp_items_per_page', 'sortasc', 'sortdesc', 'error', 'success');
			$remove_dict = [];
			foreach($items_to_remove as $item) $remove_dict[$item] = null;
			$url = getLink($url, $remove_dict);
			/*
			We are going to all this trouble for this. If the crud have multi-views - like 'downer.php?show=to_download' and 'downer.php?show=all',
				the 'show' param should be part of the $this->url. The form's action is $this->url. That way the data will be preserved.
				At the same time, we don't want stuff like id, action etc to be part of the url. 
			*/
		}
		
		$this->urls['main'] = $url;
		$this->urls['add'] = getLink($url, array('action'=>'add'));
		$this->urls['edit'] = getLink($url, array('action'=>'edit'));
		$this->urls['delete'] = getLink($url, array('action'=>'delete'));
		
		return $url;
	}

	/// Set the title of the page
	function setTitle($title, $plural = '') {
		$this->title = $title;
		if(!$plural) $this->title_plural = $title . 's';
		else $this->title_plural = $plural;
	}

	/// This function does the job of guessing what the field and value type is based on the field name, data type, validations etc.
	function _guessFieldTypes($field, $name, $data_type, $validation) {
		$field_type = false;
		$value_type = false;
		if(!$validation) $validation = [];
		
		$field_type = i($this->data_type_field_type_map, $data_type, 'text');
		$value_type = $field_type;
		
		// Special Field handles.
		if($data_type == 'enum' and strpos($field,'status') !== false) { // Status Checkbox.
			$field_type = 'checkbox';
		
		} elseif($data_type == 'varchar' and $field == 'name') {
			$validation['must'] = true;
			
		} elseif($data_type == 'varchar' and (preg_match('/\burl\b/', $field) or preg_match('/\blink\b/', $field))) { 
			$value_type = 'url';
		
		} elseif($data_type == 'varchar' and (
				(strpos($field,'image') !== false) or 
				(strpos($field,'file') !== false) or 
				(strpos($field,'path') !== false))) { 
			$value_type = 'file';
			$field_type = 'file';
			
			if(strpos($field,'image') !== false) {
				$validation['extension'] = array('png','jpg','jpeg','gif','bmp');
			}
		
		} elseif($data_type == 'datetime' and ($field == 'added_on' or $field == 'edited_on')) {
			$field_type = 'hidden';
			$value_type = 'now';
		}
		
		return array('field_type'=>$field_type, 'validation'=>$validation, 'value_type'=>$value_type);
	}
	
	////////////////////////////////////////// Action Functions //////////////////////////////////////////////
	/**
	 * This function deletes all the IDs pvodided as the argument.
	 * Arguments: $ids_to_delete - an array of IDs that must be deleted.
	 * Example: $admin->delete(array(5,4,3,2));
	 */
	function delete($ids_to_delete) {
		$to_delete_count = count($ids_to_delete);
		if(!$to_delete_count) return;
		
		global $QUERY;
		$query = "DELETE FROM {$this->table} WHERE {$this->primary_key} IN ('" . implode("','", $ids_to_delete) . "')";
		$deleted_rows = $this->execQuery($query, 'exec');
		
		if($deleted_rows == 1) $this->success = "Row deleted.";
		elseif($deleted_rows > 1 and $to_delete_count == $deleted_rows) $this->success = "$deleted_rows rows deleted.";
		elseif($to_delete_count != $deleted_rows) {
			if($to_delete_count == 1) $this->error = "Failed to delete the row.";
			else $this->error = "Failed to delete all the specified rows. $deleted_rows/$to_delete_count rows deleted.";
		}

		if(!$this->error)
			$this->postDeleteChanges($ids_to_delete);
	}
	
	/**
	 * Adds a new row to the database - using the data provided as the argument.
	 * Argument: $field_data - and associate array with all the necessary data.
	 * Example: $admin->add(array('username'=>'binnyva', 'name'=>'Binny'));
	 */
	function add($field_data) {
		global $sql, $QUERY;
		// Some fields require special handling...
		$stripped_field_data = $this->preSaveChanges($field_data);

		if($field_data) {
			$this->success = 'Added a new ' . $this->title;
			if(!empty($field_data['name'])) $this->success .= " called '$field_data[name]'";
			
			$insert_id = $sql->insert($this->table, $stripped_field_data);
			$QUERY['id'] = $insert_id; // Slightly hacky. But needed for 'Save and Continue Editing' to work

			$this->postSaveChanges($field_data, $insert_id);
			return $insert_id;
		}
		
		return false;
	}
	
	/**
	 * Edits the row with the ID given in the first argument and sets the value as the assoc array given as the second argument.
	 * Example: $admin->edit(5, array('username'=>'binnyva', 'name'=>'Binny'));
	 */
	function edit($primary_key_value, $field_data) {
		global $sql, $QUERY;
		$field_data = $this->preSaveChanges($field_data);
		if($field_data) {
			$this->success = 'Updated the ' . $this->title;
			if(!empty($field_data['name'])) $this->success .= " called '$field_data[name]'";
			
			$sql->update($this->table, $field_data, "`{$this->primary_key}`=$primary_key_value");

			$this->postSaveChanges($field_data, $primary_key_value);
			return true;
		}
		return false;
	}
	
	/** 
	 * Activate the row with the given ID
	 * Example: $admin->activate(35, 'status');
	 */
	function activate($primary_key_value, $field_name='status') {
		if(!isset($this->fields[$field_name])) return false; // Field name comes thru get - make sure its a real field.
		return $this->execQuery("UPDATE `{$this->table}` SET `$field_name`='1' WHERE `{$this->primary_key}`='$primary_key_value'", 'exec');
	}
	
	/// Disable the row with the given ID
	function deactivate($primary_key_value, $field_name='status') {
		if(!isset($this->fields[$field_name])) return false;
		$this->execQuery("UPDATE `{$this->table}` SET `$field_name`='0' WHERE `{$this->primary_key}`='$primary_key_value'", 'exec');
	}
	
	/// Change the status of the row with the given value. If it's enabled, disable it - and if it's disabled, enable it.
	function toggleStatus($primary_key_value, $field_name='status') {
		if(!isset($this->fields[$field_name])) return false;
		$this->execQuery("UPDATE `{$this->table}` SET `$field_name`=if(`$field_name`='1','0','1') WHERE `{$this->primary_key}`='$primary_key_value'", 'exec');
	}
	
	////////////////////////////////////////// Internal Helper Functions /////////////////////////////////////
	/**
	 * Some fields require special handling before its inserted into the DB. Eg - file fields should be uploaded, Date field should be converted, etc.
	 * Also, calls the validate function.
	 */
	function preSaveChanges($field_data) {
		if(!$this->validateForm()) return false;

		// Remove invalid fields(stuff not in the DB)
		$save_data = [];
		foreach($this->fields as $field_name => $field_info) {
			if(!isset($field_data[$field_name]) 				// Make sure that the field shows up in the submit list.
					and $field_info['field_type'] != 'file'	 	// File type don't show in the $_POST array
					and $field_info['field_type'] != 'checkbox'	// Checkbox won't show up if unchecked.
					) {
					
				unset($field_data[$field_name]);
				continue;
			} elseif(isset($field_data[$field_name])
					and $field_info['type'] 	== 'manytomany'	// Saved in a sepreate table.
					) {

				unset($field_data[$field_name]);
				continue;
			}
			$value = i($field_data, $field_name);

			// Changing the value depending on the type.
			switch($field_info['type']) {
				case 'datetime':
					if($field_name == 'added_on' and $field_info['field_type'] == 'hidden') {
						if($this->action == 'add_save') $value = date('Y-m-d H:i:s'); // Automatically stamp the added date/time in this field.
						
					} elseif($field_name == 'edited_on') {
						if($this->action == 'edit_save') $value = date('Y-m-d H:i:s');
					} else {
						$value = date('Y-m-d H:i:s', strtotime($value));
					}
					
					break;
				case 'date':
					$value = date('Y-m-d', strtotime($value));
					break;
				
				// File uploads.
				case 'file':
					$value = '';
					if(!empty($_FILES[$field_name]['name'])) {
						global $config;
						$valid_extension = '';
						if(isset($field_info['validation']['extension'])) $valid_extension = implode(',', $field_info['validation']['extension']);
						
						list($filename, $result) = upload($field_name, joinPath($config['site_folder'], $this->folder['uploads']), $valid_extension);
						
						if($result) return $this->validationError($field_name, $result);
						else $value = $filename;
					}
					break;
				
				case 'bit':
					if(!$value) $value = '0';
					break;

				case 'enum':
					if($field_info['value_type'] == 'status') {
						if(!$value) $value = '0';
					}
					break;
				
				// Passwords
				case 'password':
					$value = '';
					if(!empty($field_data[$field_name])) $value = empty($field_data[$field_name]);
					break;
			}
			
			// If the field is a File or password, make sure there is user entry before saving it.
			if($field_info['field_type'] == 'file' or $field_info['field_type'] == 'password') {
				if($value) $save_data[$field_name] = $value;
			} else {
				$save_data[$field_name] = $value;
			}
		}

		return $save_data;
	}

	function postSaveChanges($field_data, $element_id) {
		foreach($this->fields as $field_name => $field_info) {
			$entry_value = i($field_data, $field_name);

			// Changing the value depending on the type.
			switch($field_info['type']) {
				// Fields with referal tables
				case 'manytomany':
					extract($field_info['extra_info']);

					$where = '';
					if(isset($field_list['where'])) 
						foreach($field_list['where'] as $key => $value) 
							$where .= " AND `$key`='$value'";

					$this->execQuery("DELETE FROM $reference_table WHERE ".$field_list[1]['forign_key']."=$element_id $where");

					if($entry_value) {
						foreach($entry_value as $id) {
							$extra_key = '';
							$extra_value = '';
							if(isset($field_list['where'])) 
								foreach($field_list['where'] as $key => $value) {
									$extra_key .= ", `". $key . "`";
									$extra_value .= ", '". $value . "'";
								}

							$this->execQuery("INSERT INTO $reference_table(`".$field_list[0]['forign_key']."`, `".$field_list[1]['forign_key']."` $extra_key)
												VALUES('$id', '$element_id' $extra_value)");
						}
					}
					break;
			}
		}
	}

	/// Make these changes after a row is deleted.
	function postDeleteChanges($deleted_ids) {
		foreach($this->fields as $field_name => $field_info) {
			switch($field_info['type']) {
				// Fields with referal tables - delete the references on the deletition of the main element.
				case 'manytomany':
					extract($field_info['extra_info']);

					$where = '';
					if(isset($field_list['where'])) 
						foreach($field_list['where'] as $key => $value) 
							$where .= " AND `$key`='$value'";

					foreach($deleted_ids as $id) {
						$this->execQuery("DELETE FROM $reference_table WHERE `".$field_list[1]['forign_key']."`=$id $where");
					}
				break;
			}
		}
	}
	
	/**
	 * Validate the form submit based of the validation options specified in the field array.
	 * Return: true - if there are no errors and false if there are errors.
	 */
	function validateForm() {
		$conditions = [];
		foreach($this->fields as $field) {
			if(isset($field['validation'])) {
				$validation_rules = $field['validation'];
				
				foreach($validation_rules as $rule=>$value) {
					$conditions[] = array(
						'name'	=> $field['field'],
						'is'	=> $this->_convertValidationRule($rule),
						'value'	=> $value,
					);
				}
			}
		}
	
		$result = check($conditions, 4);
		if($result) {
			$this->validation_errors = $result;
			return false;
		}
		return true;
	}
	
	/**
	 * Convert the positive rules for validation in crud to the negative rule expected in check().
	 * For eg, the mandatory field are marked with a 'must' but in the check(), it must be 'empty'. This function takes care of the convertion between the +/- rules.
	 */
	function _convertValidationRule($rule) {
		$rule_map = array(
			'must'		=>	'empty',
			'length<'	=>	'length>',
			'length>'	=>	'length<',
		);
		return isset($rule_map[$rule]) ? $rule_map[$rule] : '';
	}
	
	/**
	 * This function caches all the validation errors and show them all at the end.
	 */
	function validationError($field_name, $error) {
		if(isset($this->validation_errors[$field_name])) $this->validation_errors[$field_name][] = $error;
		else $this->validation_errors[$field_name] = array($error);
		
		return false;
	}
	
	
	/// Process the data of the current page and convert it to a format that is usable in the Listing template.
	function makeListingDisplayData() {
		if(!$this->current_page_data) return;
		global $config;

		$total_rows = count($this->current_page_data);
		for($i=0; $i<$total_rows; $i++) {
			$row = $this->current_page_data[$i];
			
			foreach($this->listing_fields as $field_name) {
				if(!isset($this->fields[$field_name])) continue;
				$f = $this->fields[$field_name];
				$value = '';
				if(isset($row[$field_name])) $value = $row[$field_name];
				$new_value = '';

				switch($f['type']) {
					// Enum - or the listing.
					case 'enum':						
						if($f['data'] and isset($f['data'][$value])) $new_value = $f['data'][$value];
						else $new_value = $value;
						break;
					
					case 'datetime':
						if($value != '0000-00-00 00:00:00') $new_value = date($config['time_format_php'], strtotime($value));
						break;
						
					case 'date':
						if($value != '0000-00-00 00:00:00') $new_value = date($config['date_format_php'], strtotime($value));
						break;
					
					case 'virtual': //Not actually a DB column.
						$new_value = $value;

						if(isset($f['data']['html'])) {
							$new_value = eval("return " . $f['data']['html'] . ';');
							
						} elseif(isset($f['data']['function'])) {
							$new_value = '';
							if(function_exists($f['data']['function'])) {
								$new_value = call_user_func($f['data']['function'], $row);
							}
							
						} elseif(isset($f['data']['sql'])) {
							$sql = preg_replace_callback('/\%(.+?)\%/', function($m) use($row) {
								return $row[$m[1]];
							}, $f['data']['sql']);

							$new_value = $this->execQuery($sql,'one');
						}
						break;

					case 'manytomany':
						extract($f['extra_info']);
						$where = '';
						if(isset($field_list['where'])) 
							foreach($field_list['where'] as $key => $value) 
								$where .= " AND `$key`='$value'";

						$result = $this->execQuery("SELECT ref.".$field_list[0]['select']." FROM `".$field_list[0]['table']."` main 
								INNER JOIN ".$reference_table." ref ON main.id=ref.".$field_list[0]['forign_key']." 
								WHERE ref.".$field_list[1]['forign_key']."=$row[id] $where", 'byid');

						$new_value = implode("<br />", array_values($result));
						break;
			
					case 'varchar':
					default:
						$new_value = $value;
				}
				
				switch($f['field_type']) {
					case 'select':
						if($f['data'] and isset($f['data'][$value])) $new_value = $f['data'][$value];
						elseif(!$new_value) $new_value = $value;
						break;
				}
				
				switch($f['value_type']) {
					case 'url':
						$url = $value;
						if(!empty($f['data']['url'])) $url = eval('return ' . $f['data']['url'] . ';');
						if(!empty($f['data']['text'])) $value = eval('return ' . $f['data']['text'] . ';');
						$new_value = "<a href='" . $url . "'>$value</a>";
						break;
					
					case 'function':
						$new_value = $value;
						if(!empty($f['data']['function'])) {
							$new_value = call_user_func($f['data']['function'], $value);
						}
				}

				$this->current_page_data[$i][$field_name] = $new_value;
			}
		}
	}
	
	/**
	 * Use this function to set the Query that should be used in the listing page.
	 * Example: $admin->setListingQuery("SELECT * FROM User INNER JOIN People on User.id=People.user_id");
	 */
	function setListingQuery($listing_query = '', $recreate_query = false) {
		if($this->listing_query and !$recreate_query) return $this->listing_query;
		
		global $QUERY;
		
		if(!$listing_query) {
			$listing_query = "SELECT * FROM {$this->table}";
		}
		
		// Sort by specified fields
		$sort_query = '';
		if($this->allow['sorting']) {
			if(isset($QUERY['sortasc']) and $QUERY['sortasc']) {
				$sort_query = " `$QUERY[sortasc]` ASC";
				$sort_link = "sortasc=$QUERY[sortasc]&amp;";
			} elseif(isset($QUERY['sortdesc']) and $QUERY['sortdesc']) {
				$sort_query = " `$QUERY[sortdesc]` DESC";
				$sort_link = "sortdesc=$QUERY[sortdesc]&amp;";
			}
		}
		
		//If user wants to search
		$search_query = '';
		if(!empty($_REQUEST['iframe_crud_search']) and $this->allow['searching']) {
			$search_query = " `$QUERY[iframe_crud_search_in]` LIKE '%$QUERY[iframe_crud_search]%'";
			$search_link = "iframe_crud_search=$QUERY[iframe_crud_search]&amp;iframe_crud_search_in=$QUERY[iframe_crud_search_in]&";
		}

		$listing_query = $this->_addSqlFragment("WHERE",	$listing_query, $search_query);
		if($this->sort_field) $listing_query = $this->_addSqlFragment("ORDER BY",	$listing_query, " `{$this->sort_field}`");
		$listing_query = $this->_addSqlFragment("ORDER BY",	$listing_query, $sort_query);
		
		$this->listing_query = $listing_query;
		return $this->listing_query;
	}
	
	
	/////////////////////////////////////////////////// Output functions /////////////////////////////////////////////////////
	/**
	 * Sets the JS/CSS includes necessary for this page.
	 */
	function setHeaders() {
		global $config;
		$done = [];
		
		$this->_addResource('crud.css');
		if($this->action == 'edit' or $this->action == 'add') {
			$this->_addResource('form_functions.js');
			$this->_addResource('../library/validation.js');
		} elseif($this->action == 'add_save' or $this->action == 'edit_save') {
			$this->_addResource('list_functions.js');
			$this->_addResource('form_functions.js');
			$this->_addResource('../library/validation.js');
		}
		else {
			$this->_addResource('list_functions.js');
		}
		
		foreach($this->form_fields as $field_name) {
			$field_info = $this->fields[$field_name];
			extract($field_info);
			if(($type == 'datetime' or $type == 'date') and !isset($done[$type])
					and ($this->action == 'edit' or $this->action == 'add' or $this->action == 'add_save' or $this->action == 'edit_save')) {
				$this->_addResource(joinPath($config['site_url'], 'js', $this->urls['js_folder'], "jscalendar/calendar-blue.css"),	"css", true);
				$this->_addResource("jscalendar/calendar.js",		"js");
				$this->_addResource("jscalendar/calendar-en.js",	"js");
				$this->_addResource("jscalendar/calendar-setup.js", "js");
			}
			
			$done[$type] = true; // To make sure that the includes are not inserted twice.
		}
		
	}
	
	/// Prints the listing table and content. Creates the content - and then includes the template file.
	function printListing() {
		global $QUERY, $PARAM;
		$this->setListingQuery();
		$this->pager = new SqlPager($this->listing_query, $this->items_per_page);
		
		// Create the URL for the pager
		$save_params = [];
		// This states will be saved when going thru pages.
		foreach($this->save_states as $state_name) {
			if(!empty($QUERY[$state_name]) and empty($save_params[$state_name]))
				$save_params[$state_name] = $QUERY[$state_name];
		}
		
		$this->pager->page_link = getLink($this->urls['main'], $save_params);
		$this->current_page_data = $this->pager->getPage();
		$this->makeListingDisplayData();
		
		$this->listData();
	}
	function listData() {
		require('templates/Crud/listing.php');
	}
	
	/// Shows the data editing form - creates and caches the data. Then include the form template file.
	function printForm() {
		global $QUERY;
		
		if($this->action == 'edit') {
			$this->current_page_data = $this->execQuery("SELECT * FROM `{$this->table}` WHERE `{$this->primary_key}`=$QUERY[id]", "assoc");
			foreach($this->fields as $field_name => $field_info) {
				if($field_info['type'] == 'manytomany') {
					extract($field_info['extra_info']);
					$this->current_page_data[$field_name] = $this->execQuery("SELECT ".$field_list[0]['forign_key']." 
							FROM $reference_table WHERE ".$field_list[1]['forign_key']."=$QUERY[id]", "col");
				}
			}
		}
		
		require('templates/Crud/form.php');
	}
	
	
	/// This function decides which action should be shown.
	function printAction($action = '') {
		global $QUERY, $sql;
		if(!$action and !empty($_REQUEST['action'])) $action = $_REQUEST['action'];
		
		// Fixes a bug that happens when user presses Enter in the sort input box.
		if($action == 'Delete Selected' and empty($_REQUEST['select_row'])) $action = 'Sort';
		
		$this->action = $action;
		
		switch($action) {
			case 'edit':
			case 'add':
				$this->printForm();
				break;
		
			case 'add_save':
			case 'edit_save':
				if($action == 'add_save') $result = $this->add($_POST);
				else $result = $this->edit($_REQUEST['row_id'], $_POST);

				if($result) {
					if($_POST['submit'] == 'Save') {
						$this->printListing();

					} elseif($_POST['submit'] == 'Save and Continue Editing') {
						$this->current_page_data = $_POST;
						$this->action = 'edit';
						$QUERY['action'] = 'edit';
						$this->printForm();
						
					} elseif($_POST['submit'] == 'Save and Show New Form') {
						$this->action = 'add';
						$QUERY['action'] = 'add';
						global $PARAM;
						$PARAM = [];
						$this->current_page_data = [];
						$this->printForm();
					}
				} else { // Validation errors.
					$this->current_page_data = $_POST;
					$this->action = $action;
					$this->printForm();
				}
				
				break;
			
			case 'sort':
				$sort_field = '';
				foreach($this->listing_fields as $field_name) 
					if($this->fields[$field_name]['value_type'] == 'sort') 
						$sort_field = $field_name;
				
				if($sort_field) {
					foreach($QUERY['sort_row_id'] as $i=>$row_id) {
						$sql->update($this->table, array($sort_field=>$QUERY['sort_order'][$i]), "{$this->primary_key}=$row_id");
					}
				}
				$this->printListing();
				break;
			
			case 'activate':
				$status_field_name = i($QUERY, 'field_name', 'status');
				foreach($QUERY['select_row'] as $row_id) {
					$this->activate($row_id, $status_field_name);
				}
				$this->printListing();
				break;
			
			case 'deactivate':
				$status_field_name = i($QUERY, 'field_name', 'status');
				foreach($QUERY['select_row'] as $row_id) {
					$this->deactivate($row_id, $status_field_name);
				}
				$this->printListing();
				break;
			
			case 'toggle_status':
				$status_field_name = i($QUERY, 'field_name', 'status');
				foreach($QUERY['select_row'] as $row_id) {
					$this->toggleStatus($row_id, $status_field_name);
				}
				$this->printListing();
				break;
			
			case 'delete':
				$this->delete($_REQUEST['select_row']);
				// break left out intentionally - we want to list the contents after deleting a row.
	
			default:
				$this->printListing();
		}
	}
	
	/// Shows everything - not often called.
	function render($format = 'html') {
		if($format == 'csv') {
			global $QUERY, $PARAM;
			$this->setListingQuery();
			$this->pager = new SqlPager($this->listing_query, 1000000); // Disable paging by giving a very large number.
			
			$this->current_page_data = $this->pager->getPage();
			$this->makeListingDisplayData();

			require('templates/Crud/listing_csv.php');

		} else {
			showTop($this->title);
			print $this->code['top'];
			$this->printAction();
			print $this->code['bottom'];
			showEnd();
		}
	}
	
	////////////////////////////////////////////// Library Stuff //////////////////////////////
	private function execQuery($query, $type='all') {
		global $sql;
		return $sql->query($query, $type);
	}
	
	private function _getArguments($id_list) {
		$arguments = $id_list;
		if(count($arguments) == 1 and is_array($arguments[0])) { //If the first argument is the list(array) of IDs
			$arguments = $arguments[0];
		}
		return $arguments;
	}
	
	// :TODO: :UGLY: This duplicates a lot of functionality in MVC::addResource()
	private function _addResource($file, $type='', $use_exact_path = false) {
		global $template, $config;
		
		$file_name_parts = explode(".",$file);
		if(!$type) $type = array_pop($file_name_parts);
		if(preg_match('#https?\://#', $file)) $use_exact_path = true;
		
		if(!$use_exact_path) {
			$file = joinPath($type, $this->urls[$type.'_folder'], $file);
			if(preg_match('#https?\://#', $file)) $use_exact_path = true; // Starts with 'http://' - so no checks necessary.
			else {
				if(!empty($config['common_library_url'])) $file = joinPath($config['common_library_url'], $file);
				else $file = joinPath($config['site_url'], $file);
				$use_exact_path = true;
			}
		}
		$template->addResource($file, $type, $use_exact_path);
	}
	
	/**
	 * Adds an SQL fragment to an existing query. If we want to add an extra WHERE condition to the query, it will preservere the 
	 *		existing WHERE conditions and adds an extra clause after that. If there is no WHERE clause, it will add it.
	 */
	private function _addSqlFragment($replace_part, $query, $new_string) {
		if(!$new_string) return $query;
		
		//If there is already the said clause in the query, insert the new order by clause into it.
		$str_pos = strpos($query, $replace_part);
		if($str_pos)  {
			$query_start = substr($query, 0, $str_pos);
			$query_end   = substr($query, $str_pos + strlen($replace_part) , strlen($query));
			
			if($query_end) {
				if(strtolower(trim($replace_part)) == 'where') $query_end = " AND " . $query_end;
				else $query_end = ", ".$query_end;
			}
		
			$query =  $query_start . " $replace_part " . $new_string . $query_end;
		} elseif($new_string) {
			if((strtolower(trim($replace_part)) == 'where') and (stripos($query, 'ORDER BY') !== false)) {
				$query = str_replace('ORDER BY', ' WHERE ' . $new_string . ' ORDER BY', $query);
			} else {
				$query .= " $replace_part " . $new_string;
			}
		}
		
		return $query;
	}
	
}

/*
Todo
----
Editor Field Type(or use Editor for all Text data type)
Add/Edit/Delete/Status permission checks all over the place.
Design. Copy something from somewhere. Maybe implement a themeing sturucture
Login, user permissions etc.
Logging?
*/