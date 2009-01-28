<form action="generate_code.php" name="main_form" id="main_form" method="post">
<?php showStatus(); ?>
<fieldset>
<legend>Information</legend>

<?php 
$html->buildInput('title', 'Title', 'text', $field_data['title'], array(), '<span class="help">The title of the page (Eg. Users)</span>');
$html->buildInput('class_name', 'Class Name', 'text', $field_data['class_name'], array(), '<span class="help">The class of the model for this Table (Eg. User</span>');
$html->buildInput('object_name', 'Object Name', 'text', $field_data['object_name'], array(), '<span class="help">The name of the model object(Eg. $User)</span>');
$html->buildInput('table', 'Table', 'text', $field_data['table'], array(), '<span class="help">Use this database table for all operations (Eg. users)</span>');
$html->buildInput('name_single', 'Single', 'text', $field_data['name_single'], array(), '<span class="help">The singular of the page title  (Eg. User)</span>');
$html->buildInput('name_plural', 'Plural', 'text', $field_data['name_plural'], array(), '<span class="help">The plural of the page title  (Eg. Users)</span>');
?>
</fieldset>

<fieldset>
<legend>Files</legend>
<?php
$html->buildInput('controller_name', 'Controller Name', 'text', $field_data['controller_name'], array(), '<span class="help">This will be the name of the controller/template folders (Eg. user)</span>');
$html->buildInput('model_file', 'Model File', 'text', $field_data['model_file'], array(), '<span class="help">This filename will be the file name of the model file. (Eg. Users.php)</span>');
?>
</fieldset>

<fieldset>
<legend>Functionality</legend>
<?php $html->buildInput('add_funcionality', 'Add', 'checkbox', $field_data['add_funcionality'], array(), '<span class="help">Is the adding operation allowed?</span>'); ?>
<?php $html->buildInput('edit_funcionality', 'Edit', 'checkbox', $field_data['edit_funcionality'], array(), '<span class="help">Is editing operations allowed?</span>'); ?>
<?php $html->buildInput('delete_funcionality', 'Delete', 'checkbox', $field_data['delete_funcionality'], array(), '<span class="help">Is deletion allowed?</span>'); ?>
<?php $html->buildInput('status_funcionality', 'Status', 'checkbox', $field_data['status_funcionality'], array(), '<span class="help">Is status changing possible?</span>'); ?>
</fieldset>

<fieldset id="fields_area">
<legend>Fields</legend>

<?php for($field_count = 1; $field_count <= $total_fields; $field_count++) { ?>
<fieldset class="field" id="field_<?=$field_count?>">
<legend>Field <?=$field_count?></legend>

<?php
$html->buildInput('field_name_' . $field_count, 'Field', 'text', $field_data['field_name_' . $field_count], array(), '<span class="help">The database field name (Eg. user_first_name)</span>');

$all_auto_handler = array(
	'off'			=>	'None(Manual Handling)',
	'primary_key'	=>	'Primary Key Field',
	'time_of_insert'=>	'Insertion Time',
	'time_of_update'=>	'Time of Update',
	'status_holder'	=>	'Status Field',
	'current_user'	=>	'User ID Field',
);
$html->buildInput("auto_handler_$field_count", "Auto Handling", "select", $field_data['auto_handler_' . $field_count], array("options"=>$all_auto_handler), '<span class="help">You will get moore option on how to handle this field if its set to manual.</span>');
?>

<div id="field_details_<?=$field_count?>">
<?php
$html->buildInput('field_title_' . $field_count,'Title', 'text', $field_data['field_title_' . $field_count], array(), '<span class="help">The name of the field (Eg. First Name)</span>');
$html->buildInput('field_list_' . $field_count, 'List', 'checkbox', $field_data['field_list_' . $field_count], array(), '<span class="help">If checked, this field will be shown in the listing page.</span>');

$all_types = array(
	'text'		=> 'Text',
	'textarea'	=> 'Textarea',
	'list'		=> 'List',
	'file'		=> 'File',
	'date'		=> 'Date',
	'time'		=> 'Time',
	'url'		=> 'URL',
	'password'	=> 'Password',
	'editor'	=> 'Editor',
	'foreign_key'=> 'Foreign Key',
);
$html->buildInput("field_type_$field_count", "Type", "select", $field_data['field_type_' . $field_count], array("options"=>$all_types), '<span class="help">The field type - what kind of data will be stored in the field.</span>');
?>

<div class="date_options type_options">
<?php $html->buildInput('field_date_format_' . $field_count, 'Date Format', 'text', $field_data['field_date_format_' . $field_count], array(), '<span class="help">The date format - MySQL Format. If empty defaults to %Y-%m-%d (Eg. %d %b, %Y)</span>'); ?>
<?php $html->buildInput('field_show_time_' . $field_count, 'Show Time', 'checkbox', $field_data['field_show_time_' . $field_count], array(), '<span class="help">If this is checked, it gives the users the ability to input time as well as date.</span>'); ?>
</div>

<div class="password_options type_options">
<label for="field_password_encrypt_<?=$field_count?>">Encryption</label><?php 
	$all_algorithms = array(
		''		=> 'None',
		'md5'	=> 'MD5',
		'sha1'	=> 'SHA1',
		'base64_encode'=> 'Base 64'
	);
	$html->buildDropDownArray($all_algorithms, 'field_password_encrypt_' . $field_count,''); ?>
<span class="help">The password will be stored in the database after it is encrypted using the given algorithm. Do NOT use the Base 64 algorithm unless you know what you are doing.</span><br />
<?php $html->buildInput('field_password_salt_' . $field_count, 'Encryption Salt', 'text', $field_data['field_password_salt_' . $field_count], array(), '<span class="help">The salt that should be used when encrypting. Only valid for MD5 and SHA1 encryptions.</span>'); ?>
</div>
<div class="file_options type_options">
<?php $html->buildInput('field_filetype_' . $field_count, 'Allowed File types', 'text', $field_data['field_filetype_' . $field_count], array(), '<span class="help">Comma seperated list of all allowed filetypes. (Eg. jpg,gif,jpeg,png)</span>'); ?>
</div>
<div class="list_options type_options">
<?php $html->buildInput('list_values_' . $field_count, 'List Data', 'textarea', $field_data['list_values_' . $field_count], array(), '<span class="help">You can enter a SQL query that will return the data for the List or create a comma seperated list of options. (Eg: "United States","Canada","Other Country")</span>'); ?>
</div>

<div class="foreign_key_options type_options">
<?php $html->buildInput('field_foreign_key_reference_' . $field_count, 'Foreign Key Reference', 'text', $field_data['field_foreign_key_reference_' . $field_count], array(), '<span class="help">The reference to the the foreign key used here. Should be specified in this format - Table_Name.Field . Example: Team.id</span>'); ?>
</div>

<label for="field_validation_<?=$field_count?>">Validation</label><?php 
	$all_algorithms = array(
		'must'		=> 'Must',
		'email'		=> 'Email',
		'filetype'	=> 'File Type',
		'number'	=> 'Number',
		'unique'	=> 'Unique',
		'url'		=> 'URL',
	);
	$html->buildDropDownArray($all_algorithms, 'field_validation_' . $field_count,'',array('multiple'=>'multiple','name'=>'field_validation_' . $field_count . '[]')); ?>
<span class="help">Which all validation should be applied to the field.</span><br />
</div>
</fieldset>
<?php } ?>

<br />

<div id="extra_fields"></div>

<input type="button" name="new_field" value="Add more fields" onclick="newField();" />
<input type="hidden" name="total_fields" id="total_fields" value="<?=$total_fields?>"  />
</fieldset>


<fieldset>
<legend>Extra Information</legend>

<?php
$html->buildInput('pager_status', 'Enable Pager?', 'checkbox', '1', array('checked'=>'checked'), '<span class="help">Enable the pager</span>');
$html->buildInput('pager_items_per_page', 'Items Per Page', 'text', '20', array(), '<span class="help">The number of items that should be shown in each page</span>');
$html->buildInput('upload_path', 'Upload Location', 'text', '../uploads', array(), '<span class="help">The folder where all the files must be upload to. Make sure that there is a "/" at the end.</span>');
$html->buildInput('mandatory_text', 'Mandatory Text', 'text', '*', array(), '<span class="help">The text that should be shown next the each mandateory field (Eg. *)</span>');
$html->buildInput('main_query', 'Main Query', 'textarea', '', array(), '<span class="help">The query that must be used when creating the display. If empty, this will default to "SELECT * FROM &lt;Table&gt;"</span>');
?>
</fieldset>

<fieldset class="normal-form">
<legend>Files to Generate</legend>

<?php foreach($available_templates as $template_file) {
	$dependancy_functionality = '';
	
	if(strpos($template_file, 'add')) $dependancy_functionality = 'Make sure the "Add" functionality is turned on for this.';
	if(strpos($template_file, 'edit')) $dependancy_functionality = 'Make sure the "Edid" functionality is turned on for this to work as intended.';
	if(strpos($template_file, 'delete')) $dependancy_functionality = 'Turn on the "Delete" functionality or this may not work.';
	
	$html->buildInput(str_replace(array('/','.'),'_', $template_file), $template_file, 'checkbox', $template_file, 
		array('checked'=>'checked', 'name'=>'generate_files[]'),
		'<span class="help">Makes sure that this file is generated. ' . $dependancy_functionality . '</span>');
} ?>

</fieldset>

<div id="information">
<input type="submit" value="Create Code" name="action" />
<input type="reset" value="Clear All Fields" id="clearer" />
<br /><br />
</div>

</form>

<hr />

<div id="serialized-data">
<div class="entry"><label for="adm_upload">Serialized Data Input</label>
<textarea name="serialized" id="serialized" rows="10" cols='100'>
</textarea>
<span class="help">Enter the data at the end of the generated PHP file here to re-populate the form with the old page details.</span></div>
<input type="button" value="Parse Serialized Data" id="parse-serialized-data" />
</div>


<div id="tip-holder" class="rounded-corner"><div id="tip">Help!</div></div>