<?php
require('iframe.php');
require_once('/var/www/html/Others/Library/functions/iframe_helpers.php');
// Org Location - file:///mnt/x/Data/www/tools/Twitter/index.php
// http://localhost/tools/Twitter/

$sql = new Sql('Data');

$twitter = new Crud('Twitter');
$twitter->guess();

// Convert the content of the column to a URL.
$twitter->addField('username', 'User Name', 'varchar', array(), 
		array('url'=>'"http://twitter.com/$row[username]"', 'text'=>'"@$row[username]"'),'text', 'url');

$twitter->addField('reply_to', 'Conversation', 'varchar', array(), 
		array('url'=>'"conversation.php?tweet_id=$row[id]"', 'text'=>'"See Conversation"'),'text', 'url');

$twitter->addField('added_on', 'Time', 'datetime', array(), 
		array('url'=>'"http://twitter.com/$row[username]/status/$row[id]"', 'text'=>'date("jS M y, h:i a", strtotime($row["added_on"]))'),'text', 'url');

// Change content based on a callback function.
$twitter->addField('tweet', 'Tweet', 'varchar', array(), 
		array('function'=>'linkfyTweet'), 'function', 'function');
		
$date_where = '';
if(i($QUERY, 'date')) {
	$date_where = " AND DATE(added_on)='$QUERY[date]'";

	$next_day = strtotime($QUERY['date']) + (24 * 60 * 60);
	$last_day = strtotime($QUERY['date']) - (24 * 60 * 60);
	
	$twitter->code['bottom'] = "<a href='".getLink('index.php', array('date'=>date('Y-m-d',$last_day)), true)."' class='with-icon previous'>".date('dS M',$last_day)."</a>";
	$twitter->code['bottom'].= "<a href='".getLink('index.php', array('date'=>date('Y-m-d',$next_day)), true)."' class='with-icon next' style='float:right;'>".date('dS M',$next_day)."</a>";
}


if(!empty($_REQUEST['show']) and $_REQUEST['show'] == 'mentions') {
	$twitter->setListingQuery("SELECT tweet, added_on, id, username FROM Twitter 
								WHERE username!='' AND added_on != '0000-00-00 00:00:00' $date_where ORDER BY added_on DESC");
	$twitter->setListingFields('tweet', 'username','reply_to', 'added_on');

} elseif(!empty($_REQUEST['show']) and $_REQUEST['show'] == 'nonmentions') {
	$twitter->setListingQuery("SELECT tweet, added_on, id, username FROM Twitter 
								WHERE username='' AND added_on != '0000-00-00 00:00:00' $date_where AND tweet NOT LIKE '@%' ORDER BY added_on DESC");
	$twitter->setListingFields('tweet','reply_to', 'added_on');

} else {
	$twitter->setListingQuery("SELECT tweet, added_on, id, 'binnyva' AS username FROM Twitter 
								WHERE username='' AND added_on != '0000-00-00 00:00:00' $date_where ORDER BY added_on DESC");
	$twitter->setListingFields('tweet','reply_to', 'added_on');
}

$twitter->code['top'] = '<ul id="show-filter">
<li><a href="'.getLink('index.php', array('show'=>'all'), true).'">All</a></li>
<li><a href="'.getLink('index.php', array('show'=>'nonmentions'), true).'">Message</a></li>
<li><a href="'.getLink('index.php', array('show'=>'mentions'), true).'">Other\'s Mentions</a></li>
</ul>';

$twitter = centralizeCrudLinks($twitter);
$twitter->allow['bulk_operations'] = false;
$twitter->allow['add'] = false;
$twitter->allow['edit'] = false;
$twitter->allow['delete'] = false;

$twitter->render();

function linkfyTweet($tweet_text) {
	return preg_replace(array(
			'/http([^\s]+)/',
			'/@(\w+)/',
			'/#(\w+)/',
		), array(
			"<a href='http$1'>http$1</a>",
			"<a href='http://twitter.com/$1'>@$1</a>",
			"<a href='http://search.twitter.com/search?q=%23$1'>#$1</a>",
		), $tweet_text);
}
