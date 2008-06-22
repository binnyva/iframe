<?php
$var = '$' . strtolower($name_single);
?>
<h1><?=$name_plural?></h1>

<table>
<?='<'?>?php
$row = 0;
foreach($<?=strtolower($name_plural)?> as <?=$var?>) {
	$class = ($row++ % 2) ? 'even' : 'odd';
	$id = <?=$var?>['<?=$primary_key?>'];
?>
<tr class="<?='<'?>?=$class?>">
<?php foreach($fields as $f) {
	if(!$f['list']) continue;
	$fn = $f['field'];
?>
<td><?='<'?>?=<?=$var?>['<?=$fn?>']?></td>
<?php } ?>

<?php if($functionality['edit']) { ?><td class="action"><a class="icon edit" href="edit.php?id=<?='<'?>?=$id?>">Edit</a></td><?php } ?>

<?php if($functionality['delete']) { ?><td class="action"><a class="icon delete" href="delete.php?id=<?='<'?>?=$id?>">Delete</a></td><?php } ?>
</tr>
<?='<'?>?php } ?>
</table>
<?='<'?>?php showPager(); ?>


<?php if($functionality['add']) { ?><a class="icon new" href="new.php">New <?=$title?></a><?php } ?>