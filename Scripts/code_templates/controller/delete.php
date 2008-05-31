<?='<'?>?php
include('../common.php');

if(isset($QUERY['id']) and is_numeric($QUERY['id'])) {
	<?= $object_name ?>->remove($QUERY['id']);

	showMessage("<?= $title ?> deleted successfully",'index.php');
}
