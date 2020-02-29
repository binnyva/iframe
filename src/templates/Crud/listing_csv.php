<?php
if($this->allow['header']) {
	$headers = array();
	foreach($this->listing_fields as $field_name) {
		if(!isset($this->fields[$field_name])) continue;
		$headers[] = '"' . $this->fields[$field_name]['name'] . '"';
	}

	print implode(",", $headers) . "\n";
}

// Data
foreach($this->current_page_data as $row_data) {
	$row = array();
	foreach($this->listing_fields as $field_name) {
		if(!isset($this->fields[$field_name])) continue;
		$row[] = '"' . $row_data[$field_name] . '"';
	}
	print implode(",", $row) . "\n";
}
