<?php echo '<' ?>?php
require('./common.php');

$admin = new Crud("<?php echo $table ?>");

<?php
print $data_fetches;
print "\n";
print $field_area;
?>

$admin->render();
