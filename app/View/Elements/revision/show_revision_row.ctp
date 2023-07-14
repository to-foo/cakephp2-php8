<?php if(empty($data['Revision']['row'])) return;?>

<div>
<?php
switch($data['Revision']['action']) {

	case 'editevaluation':
    echo $this->element('revision/show_revision_editevaluation',array('data' => $data));
	break;

    case 'edit':
    echo $this->element('revision/show_revision_editevaluation',array('data' => $data));
    break;
    
    case 'images/discription':
    echo $this->element('revision/show_revision_image_description',array('data' => $data));
    break;

    case 'files/discription':
    echo $this->element('revision/show_revision_file_description',array('data' => $data));
    break;
}

?>
</div>