<?php
if(!isset($this->request->data[$Model])) return;
if(!isset($this->request->data[$Model][$Field])) return;

if(count($this->request->data[$Model][$Field]['value']) == 1 && isset($this->request->data[$Model][$Field]['value'][0]) && empty($this->request->data[$Model][$Field]['value'][0])) return;

echo $this->Form->input($this->request->data[$Model][$Field]['description'],array(
	'class' => 'dropdown',
	'label' => $local,
	'id' => $Model.$Field,
	'name' => 'data['.$Model.']['.$field.']',
	'options' => $this->request->data[$Model][$Field]['value'],
	'selected' => $this->request->data[$Model][$Field]['selected'],
	$IsDisabled
	)
);
?>
