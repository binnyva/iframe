<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Create Admin Page</title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="css/index.css" rel="stylesheet" type="text/css" />
<script src="js/prototype.js" type="text/javascript"></script>
<script src="js/application.js" type="text/javascript"></script>
<script src="js/index.js" type="text/javascript"></script>
</head>
<body>
<div id="header">
	<h1>Create Admin Page</h1>
</div>

<div id="content">

<form action="" name="main_form" id="main_form">
<?php showStatus(); ?>
<fieldset>
<legend>Information</legend>

<?php 
$html->buildInput('title', 'Title', 'text', $field_data['title'], array(), '<span class="help">The title of the page (Eg. Users)</span>');
$html->buildInput('file', 'File', 'file', $field_data['file'], array(), '<span class="help">This filename will be used in all applications (Eg. users.php)</span>');
$html->buildInput('table', 'Table', 'text', $field_data['table'], array(), '<span class="help">Use this database table for all operations (Eg. users)</span>');
$html->buildInput('single', 'Single', 'text', $field_data['single'], array(), '<span class="help">The singular of the page title  (Eg. User)</span>');
$html->buildInput('primary_key', 'Primary Key', 'text', $field_data['primary_key'], array(), '<span class="help">The primary key of the table that is used (Eg. user_id)</span>');
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

<?php for($field_count = 1; $field_count < $total_fields; $field_count++) { ?>
<fieldset class="field" id="field_<?=$field_count?>">
<legend>Field <?=$field_count?></legend>
<?php 
$html->buildInput('field_title_' . $field_count, 'Title', 'text', $field_data['field_title_' . $field_count], array(), '<span class="help">The name of the field (Eg. First Name)</span>');
$html->buildInput('field_name_' . $field_count, 'Field', 'text', $field_data['field_name_' . $field_count], array(), '<span class="help">The database field name (Eg. user_first_name)</span>');
$html->buildInput('field_list_' . $field_count, 'List', 'checkbox', $field_data['field_list_' . $field_count], array(), '<span class="help">If checked, this field will be shown in the listing page.</span>');
?>
<label for="field_type_<?=$field_count?>">Type</label><?php 
	$all_types = array(
		'text'		=> 'Text',
		'textarea'	=> 'Textarea',
		'list'		=> 'List',
		'file'		=> 'File',
		'date'		=> 'Date',
		'time'		=> 'Time',
		'password'	=> 'Password',
		'editor'	=> 'Editor'
	);
	$html->buildDropDownArray($all_types, 'field_type_' . $field_count,'text'); ?>
<span class="help">The field type - what kind of data will be stored in the field.</span><br />

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
		'base64'=> 'Base 64'
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

<label for="field_validation_<?=$field_count?>">Validation</label><?php 
	$all_algorithms = array(
		'must'		=> 'Must',
		'email'		=> 'Email',
		'filetype'	=> 'File Type',
		'number'	=> 'Number',
		'unique'	=> 'Unique'
	);
	$html->buildDropDownArray($all_algorithms, 'field_validation_' . $field_count,'',array('multiple'=>'multiple','name'=>'field_validation_' . $field_count . '[]')); ?>
<span class="help">Which all validation should be applied to the field.</span><br />
</fieldset>
<?php } ?>

<br />

<div id="extra_fields"></div>

<input type="button" name="new_field" value="Add more fields" onclick="newField();" />
</fieldset>

<!--
<?php $html->buildInput('', '', 'text', '', array(), '<span class="help"></span>'); ?>
<?php $html->buildInput('', '', 'text', '', array(), '<span class="help"></span>'); ?>
<?php $html->buildInput('', '', 'text', '', array(), '<span class="help"></span>'); ?>

<fieldset>
<legend>Extra Information</legend>
<div class="entry"><label for="adm_status_field">Status Field</label>
<%= text_field 'adm', 'status_field', {"value"=>'status'}  %>
<span class="help">The name of the status field in the database.</span></div>

<div class="entry"><label for="adm_pager">Show Pager</label>
<%= check_box 'adm', 'pager', {'checked' => 'checked'}, "true", "false" %>
<span class="help">Enable the pager</span></div>

<div class="entry"><label for="adm_upload">Upload Location</label>
<%= text_field 'adm', 'upload',  {"value"=>'../uploads'}  %>
<span class="help">The folder where all the files must be upload to. Make sure that there is a '/' at the end.</span></div>

<div class="entry"><label for="adm_mandatory_text">Mandatory Text</label>
<%= text_field 'adm', 'mandatory_text',  {"value"=>' <span class="mandatory">*</span>'}  %>
<span class="help">The text that should be shown next the each mandateory field (Eg. *)</span></div>

<div class="entry"><label for="adm_main_query">Main Query</label>
<%= text_area 'adm', 'main_query', { 'rows' => "5" } %>
<span class="help">The query that must be used when creating the display. If empty, this will default to 'SELECT * FROM &lt;Table&gt;'</span></div>

-->

<div id="information">
<input type="submit" value="Create Code" name="action" />
<input type="reset" value="Clear All Fields" id="clearer" />
<br /><br />
</div>

<div id="tip-holder" class="rounded-corner"><div id="tip">Help!</div></div>
</form>

<hr />

<div id="serialized-data">
<div class="entry"><label for="adm_upload">Serialized Data Input</label>
<textarea name="serialized" id="serialized" rows="10" cols='100'></textarea>
<span class="help">Enter the data at the end of the generated PHP file here to re-populate the form with the old page details.</span></div>
<input type="button" value="Parse Serialized Data" id="parse-serialized-data" />
</div>
</div>

<div id="footer">
</div>

</body>
</html>
