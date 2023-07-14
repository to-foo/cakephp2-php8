<?php

$action = $this->request->params['action'];

$x = 0;

foreach($this->request->data['xml']['settings']->section->item as $_key => $_xml){

	$ShowThis = false;

	foreach($_xml->show->children() as $__key => $__value){
		if(trim($__value) == $action){
			$ShowThis = true;
			break;
		}
	}

	if($ShowThis == false) continue;

	$x++;

	$class= null;



	if(!empty($_xml->class)) $class = trim($_xml->class);
	echo '<td class="'.$class.'">';
	echo '<span class="discription_mobil">';
	echo trim($_xml->description->$locale);
	echo '</span>';
	echo h((trim($_examiner[trim($_xml->model)][trim($_xml->key)])));
	echo '</td>';
}
return;
?>
