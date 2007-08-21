<h1>Projects</h1>

<table>
<?='<'?>?php 
$row = 0;
foreach($projects as $id=>$name) {
$class = ($row++ % 2) ? 'even' : 'odd';
?>
<tr class="<?='<'?>?=$class?>"><td id="cell_<?='<'?>?=$id?>"><a class="with-icon project" href="../tasks/list.php?project=<?='<'?>?=$id?>"><?='<'?>?=$name?></a></td>
<td class="action"><a class="icon edit" href="edit.php?project=<?='<'?>?=$id?>">Rename</a></td>
<td class="action"><a class="icon delete" href="delete.php?project=<?='<'?>?=$id?>">Delete</a></td></tr>
<?='<'?>?php } ?>
</table>
<?='<'?>?php showPager(); ?>

<form action="new.php" id="new_item" method="post">
<label>New Project</label><input type="text" id="name" name='name' />
<input name="action" value="Create" type='submit' />
</form>
