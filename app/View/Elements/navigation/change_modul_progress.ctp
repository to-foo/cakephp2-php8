<?php
if(!isset($ModalLinks)) return;
if(count($ModalLinks) == 0) return;

$options = array();
$selected = null;

foreach($ModalLinks as $key => $value){

	$options[$value['url']] = $value['name'];
	if($value['selected'] == 1) $selected = $value['url'];

}

echo $this->Form->input('ChangeModul',array(
		'label' => false,
		'div' => false,
		'type' => 'select',
		'selected' => $selected,
		'options' => $options,
		'class' => 'select_standard'
		)
	);

echo $this->element('navigation/js/change_modul_progress_js');

?>
