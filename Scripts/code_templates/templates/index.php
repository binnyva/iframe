<?php
$var = '$' . strtolower($PARAM['name_single']);
?>
<h1><?= $PARAM['name_plural'] ?></h1>

<table class="listing-table">
<tr><?php foreach($fields as $f) { //Show Headers
	if($f['list']) print "<th>$f[title]</th>";
} ?></tr>
<?='<'?>?php
$row = 0;
foreach($<?=strtolower($PARAM['name_plural'])?> as <?=$var?>) {
	$class = ($row++ % 2) ? 'even' : 'odd';
	$id = <?=$var?>['<?=$primary_key?>'];
?>
<tr class="<?='<'?>?=$class?>">
<?php foreach($fields as $f) {
	if(!$f['list']) continue;
	$fn = $f['field'];
	
	print "<td>";
	if($f['type'] == 'list' or $f['type'] == 'foreign_key') { //If it a list, arrange to get the foreign refenences.
		print "<?=$" . $f['field'] ."_list[${var}['$fn']]?>";
	} else {
		print "<?=${var}['$fn']?>";
	}
	print "</td>\n";
} ?>

<?php if($functionality['edit']) { ?><td class="action"><a class="icon edit" href="edit.php?id=<?='<'?>?=$id?>">Edit</a></td><?php } ?>
<?php if($functionality['delete']) { ?><td class="action"><a class="icon delete" href="delete.php?id=<?='<'?>?=$id?>">Delete</a></td><?php } ?>
</tr>
<?='<'?>?php } ?>
</table>
<?='<'?>?php showPager(); ?>


<?php if($functionality['add']) { ?><a class="icon new" href="new.php">New <?=$title?></a><?php } ?>

<?php
/* :TODO
 * Table Headers.
 * User Dependant Functionality changes(edit,delete,add)
 * Support for different format data.
 */
