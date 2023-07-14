<?php
if(!isset($ModalLinks)) return;
if(count($ModalLinks) == 0) return;

$options = array();
$selected = null;

echo $this->Form->create('ChangeModul',array('class' => 'quip_search_form'));

foreach($ModalLinks as $key => $value){

	if(isset($value['post']) && count($value['post']) > 0){

		foreach ($value['post'] as $_key => $_value) {

			echo $this->Form->input($_key,array('type' => 'hidden','value' => $_value));

		}
	}

	$options[$value['url']] = $value['name'];
	if($value['selected'] == 1) $selected = $value['url'];

}

echo $this->Form->input('ChangeModul',array(
		'label' => false,
		'div' => false,
		'type' => 'select',
		'selected' => $selected,
		'options' => $options,
		'class' => ''
		)
	);

echo $this->Form->end();

echo $this->element('navigation/js/change_modul_ndt_js');

?>
