<?='<'?>?php
include('../common.php');

if(isset($QUERY['action']) and $QUERY['action']=='Create') {
	if(!$QUERY['name'])	showMessage("Please provide the name of the new <?= $Controller['name'] ?>",'','error');

	if($id = <?= $Model['object_name'] ?>->create($QUERY['name'])) {
		showMessage("<?= ucfirst($Controller['name']) ?> '$PARAM[name]' created successfully","index.php",'success',$id);
	}
}

render();