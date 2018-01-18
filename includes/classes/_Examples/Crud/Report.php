<?php
/**
 * Crud
 * Purpose : The aim of this file is to provide some example uses for the Crud Class.
 */

// Use Crud as a report generation tool.

$crud = new Crud("UserEvent");
$where_choice = '';
if($user_choice) $where_choice = " AND UE.user_choice='$user_choice'";

// Set a custom query to get the report data
$crud->setListingQuery("SELECT U.id,U.name,U.phone,U.email, UE.user_choice
	FROM `UserEvent` UE 
	INNER JOIN User U ON U.id=UE.user_id
	WHERE UE.event_id = $event_id AND U.user_type='volunteer' AND U.status='1' $where_choice
	ORDER BY U.name");
$crud->setListingFields("name",'phone','email','user_choice');
$crud->title = 'Event Invitation Report';

// Show different titles based on the value in the row. In this case, value of the 'user_choice' field. $event_status gives the titles. It can be an associative array.
$event_status = array('Invited - Not marked', 'Coming', 'Maybe', 'Not coming');
$crud->addField("user_choice", 'User Choice', 'enum', array(), $event_status);

// Disable all Crud operations. Makes it just a report.
$crud->allow['bulk_operations'] = false;
$crud->allow['add'] = false;
$crud->allow['edit'] = false;
$crud->allow['delete'] = false;

// Add more display text.
$show = "<h3>Filter - show only...</h3>";
$show .= "<ul>";
foreach ($event_status as $key => $value) {
	$show .= "<li><a href='?event_id=$event_id&user_choice=$key'>$value</a></li>\n";
}
$show .= "</ul>";
$crud->code['before_content'] = $show;

$crud->render();

// ------------------------------------------------------------------------------------------------

