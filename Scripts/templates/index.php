Generate the code for the table...

<ul>
<?php foreach($tables as $t) { ?>
<li><a href="options.php?table=<?=$t?>"><?=format($t)?></a></li>
<?php } ?>
</ul>