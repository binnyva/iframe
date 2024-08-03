<?php
global $PARAM;
$html = new iframe\HTML\HTML;

// :TODO: Convert the form to bootstrap type - use class=form-control
?>
<h2 class="action-title"><?php echo ucfirst($this->action) . ' ' . $this->title?></h2>

<form action="<?php echo $this->urls['main']?>" id="admin-form" method="post" class="form-area" enctype="multipart/form-data">
<fieldset>
<?php
$js_code = '';
$row_data = $this->current_page_data;
if(empty($row_data)) $row_data = $_GET;

foreach($this->form_fields as $field_name) {
	if(!isset($this->fields[$field_name])) continue;
	
	$field_info = $this->fields[$field_name];
	extract($field_info);
	$value = i($row_data, $field);

	// This field is read only.
	if(i($extra_info,'edit', true) === false or i($extra_info,'readonly')) {
		if($field_type == 'select') $value = $data[$value];
		$field_type = 'span';
	}
	
	if($field_type != 'hidden') print "<div class='field-area' id='{$field}_area'>";
	
	// Enum - Select tag
	if($field_type == 'select') {
		$attributes = array('options' => $data);
		if($type == 'multiselect' or $type == 'manytomany') {
			$attributes['multiple'] = 'multiple';
			$attributes['size'] = 10;
			$attributes['name'] = $field.'[]';
		}
		$html->buildInput($field, $name, 'select', $value, $attributes);
	
	// Date Field.
	} elseif($field_type == 'datetime' or $field_type == 'date') {
		if($field_type == 'datetime') $format = "Y-m-d\TH:i";
		else $format = "Y-m-d";
		
		if($value and $value != '0000-00-00 00:00:00' and $value != '0000-00-00') $value = date($format, strtotime($value));
		elseif($this->action == 'add' and in_array($field, $this->default_field_names['added_timestamp'])) $value = date($format); // Its the good old added_on field. If we are creating a new row, put the current time there.
		elseif($this->action == 'edit' and in_array($field, $this->default_field_names['edited_timestamp'])) $value = date($format); // Same for edited_on field.
		else $value = '';
		$value = preg_replace('/<.+?>[^>]+>/','', $value); // Remove the tags. Things like <sup>th</sup>
		
		$html_field_type = $field_type;
		if($field_type === 'datetime') $html_field_type = 'datetime-local';
		$html->buildInput($field, $name, $html_field_type, $value);

	} elseif($field_type == 'hidden') {
		$hidden_value = $value ? $value : $data;
		if(!$hidden_value) $hidden_value = '';

		$html->buildInput($field, '', 'hidden', $hidden_value);

	} else {
		if($field_type == 'checkbox' or $field_type == 'radio' or $field_type == 'textarea') $attributes = array();
		else $attributes = array('class'=>'text-long');

		if($field_type == 'checkbox') $attributes['label_first'] = true;
		
		if($data and !$value) $value = $data;
		if(is_array($value)) $value = i($value, 'data', '');
		if(!empty($PARAM[$field])) $value = $PARAM[$field];

		$html->buildInput($field, $name, $field_type, $value, $attributes);
	}
	if(isset($this->validation_errors[$field])) {
		if(count($this->validation_errors[$field]) > 1) {
			print "<ul class='error-message validation-error'><li>" . implode("</li><li>",  $this->validation_errors[$field]) . "</li></ul>";
		}
		else print "<span  class='error-message validation-error'>". $this->validation_errors[$field][0] . "</span>";
	}
	
	if($field_type != 'hidden') print "</div>\n";
}
$html->buildInput("row_id", "", "hidden", i($QUERY, 'id'));
$html->buildInput("id", "", "hidden", i($QUERY, 'id'));

$save_current_state = array('search','search_in', 'sp_page','sp_items_per_page', 'sortasc', 'sortdesc');
foreach($save_current_state as $state_name) {
	if(!empty($QUERY[$state_name]))
		$html->buildInput($state_name, "", "hidden", $QUERY[$state_name]);
}

// The action area.
print "<div class='action-area'>";
if(($QUERY['action'] == 'edit' or $QUERY['action'] == 'edit_save') and $this->allow['delete'])
	print "<a href='" . getLink($this->urls['main'], array('select_row[]'=>i($QUERY, 'id'), 'action'=>'delete')) . "' title='Delete this row' class='btn btn-sm btn-danger delete-current-item confirm float-right'>Delete</a>";

$html->buildInput("action-save", "&nbsp;", "submit", 'Save', ['name' => 'submit', 'class'=>'action-submit btn btn-success', 'no_br' => true]);
if($this->allow['save_and_edit_form_button']) print " &nbsp; <input type='submit' id='action-save-edit' name='submit' class='action-submit btn btn-secondary' value='Save and Continue Editing' />";
if($this->allow['save_and_new_form_button']) print " &nbsp; <input type='submit' id='action-save-new' name='submit' class='action-submit btn btn-light' value='Save and Show New Form' />";

print "</div>";

if($QUERY['action'] == 'edit' or $QUERY['action'] == 'add') $form_action = $QUERY['action'] . "_save";
else $form_action = $QUERY['action'];
$html->buildInput("action", "", "hidden", $form_action);
?>
</fieldset>
</form>

<script type="text/javascript">
function validate(e) {
	var success = false;
	<?php
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
	if(isset($conditions)) print "success = check(" . json_encode($conditions) . ", 1);";
	else print "success = true; // No conditions found\n";
	?>
	if(!success) e.stopPropagation();
	return success;
}
function start() {
	$("#admin-form").submit(validate);
<?php echo $js_code?>
}
window.onload=start;
</script>

<?php
function jsDateFormat($format_string) {
	$replace_rules = array(
		'%b' => '%M',
		'%p' => '%a',
		'%P' => '%A',
		'%D' => '%d',
	);

	return str_replace(array_keys($replace_rules), array_values($replace_rules), $format_string);
}
