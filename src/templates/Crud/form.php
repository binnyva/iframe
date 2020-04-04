<?php
global $PARAM;
$html = new iframe\HTML\HTML;

// :TODO: Convert the form to bootstap type - use class=form-control
// :TODO: Change date to type=date instead of using js calendar.
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
		if($field_type == 'datetime') $js_date_format = \iframe\App::config['time_format'];
		else $js_date_format = \iframe\App::config['date_format'];
		
		if($value and $value != '0000-00-00 00:00:00' and $value != '0000-00-00') $value = date(phpDateFormat($js_date_format), strtotime($value));
		elseif($this->action == 'add' and $field == 'added_on') $value = date(phpDateFormat($js_date_format)); // Its the good old added_on field. If we are creating a new row, put the current time there.
		elseif($this->action == 'edit' and $field == 'edited_on') $value = date(phpDateFormat($js_date_format)); // Same for edited_on field.
		else $value = '';
		$js_date_format = jsDateFormat($js_date_format);
		$value = preg_replace('/<.+?>[^>]+>/','', $value); // Remove the tags. Things like <sup>th</sup>
		
		$html->buildInput($field, $name, 'text', $value, array('class'=>'text-long'), "<input class='button' type='button' value=' ... ' id='date_button_$field' />");
		$print_time = 1;
		
		// I use this method to make sure this is called only after the calendar.js files are loaded.
		$js_code .= <<<JS_END
		Calendar.setup({
			inputField	: "$field",				// id of the input field
			ifFormat	: "$js_date_format",	// format of the input field
			printsTime	: $print_time,			// will display a time selector
			button		: "date_button_$field",	// trigger for the calendar (button ID)
			singleClick	: true,	// double-click mode
			timeFormat	: 12,	// The time format - 12 hr clock or the 24 Hr clock.
			step		: 1		// print all years in drop-down boxes (instead of every other year as default)
		});

JS_END;
	
	} elseif($field_type == 'hidden') {
		$hidden_value = $value ? $value : $data;
		if(!$hidden_value) $hidden_value = '';

		$html->buildInput($field, '', 'hidden', $hidden_value);

	} else {
		if($field_type == 'checkbox' or $field_type == 'radio' or $field_type == 'textarea') $attributes = array();
		else $attributes = array('class'=>'text-long');
		
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

print "<input type='submit' id='action-save' name='submit' class='action-submit btn btn-success' value='Save' />";
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
	else print "success = true; // No conditios found\n";
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
