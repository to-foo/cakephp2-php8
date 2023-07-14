<?php
echo '<fieldset class="multiple_field">';
echo $this->Form->input($this->request->data[$Model][$Field]['description'],array(
	'class' => 'multiselect',
	'multiple' => 'multiple',
	'label' => $local,
	'id' => $Model.$Field,
	'name' => 'data['.$Model.']['.$field.']',
	'options' => $this->request->data[$Model][$Field]['value'],
	'selected' => $this->request->data[$Model][$Field]['selected'],
	$IsDisabled
	)
);
echo '</fieldset>';
?>
