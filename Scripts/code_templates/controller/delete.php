<?='<'?>?php
include('../common.php');

if(isset($QUERY['<?= $Controller['name'] ?>']) and is_numeric($QUERY['<?= $Controller['name'] ?>'])) {
	$<?= $Controller['name'] ?>_name = $<?= $Model['object_name'] ?>->find($QUERY['<?= $Controller['name'] ?>'],array('select'=>array('name'),'result_type'=>'one')));

	$<?= $Model['object_name'] ?>->remove($QUERY['<?= $Controller['name'] ?>']);

	showMessage("<?= ucfirst($Controller['name']) ?> '" . $<?= $Controller['name'] ?>_name ."' deleted successfully",'index.php');
}
