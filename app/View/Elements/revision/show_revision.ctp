<?php if(!empty($data['Revision']['row'])) return;?>
<div>
<?php
switch($data['Revision']['action']) {

	case 'deleteevalution/weld':
    echo $this->element('revision/show_revision_deleteevaluation',array('data' => $data));
	break;

	case 'duplicatevalution/weld':
	echo $this->element('revision/show_revision_duplicateevaluation',array('data' => $data));
	break;

	case 'images/addimage':
	echo $this->element('revision/show_revision_add_image',array('data' => $data));
	break;

	case 'images/delimage':
	echo $this->element('revision/show_revision_del_image',array('data' => $data));
	break;

	case 'files/addfile':
	echo $this->element('revision/show_revision_add_file',array('data' => $data));
	break;
	
	case 'files/delfile':
	echo $this->element('revision/show_revision_del_file',array('data' => $data));
	break;
}

?>
</div>