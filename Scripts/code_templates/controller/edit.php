<?='<'?>?php
include('../common.php');

$<?= $Controller['name'] ?>_data = $<?= $Controller['name'] ?>_name = $<?= $Model['object_name'] ?>->find($QUERY['<?= $Controller['name'] ?>'],array('select'=>array('id','name'),'result_type'=>'assoc')));

if(isset($QUERY['action']) and $QUERY['action']=='Edit') {
	if(!$QUERY['name'])	showMessage("Please provide the new name",'?project='.$QUERY['<?= $Controller['name'] ?>'],'error');

	if($<?= $Model['object_name'] ?>->edit($QUERY['<?= $Controller['name'] ?>'], $QUERY['name'])) {
		showMessage("<?= ucfirst($Controller['name']) ?> '" . $<?= $Controller['name_plural'] ?>[$QUERY['<?= $Controller['name'] ?>']] . "' updated successfully",'index.php');
	}
} else {
	render();
}