<tr>
<?php

$action = $this->request->params['action'];

echo '<th class="">' . __('Description',true) . '</th>';
echo '<th class="">' . __('Expediting',true) . '</th>';

foreach($this->request->data['xml']['settings']->section->item as $_key => $_xml){

	$ShowThis = false;

	foreach($_xml->show->children() as $__key => $__value){
		if(trim($__value) == $action){
			$ShowThis = true;
			break;
		}
	}

	if($ShowThis == false) continue;

	$class = null;
	if(!empty($_xml->class)) $class = trim($_xml->class);
	echo '<th class="'.$class.'">';
	echo trim($_xml->description->$locale);
	echo '</th>';
}
?>
</tr>
