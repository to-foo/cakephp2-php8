<?php
echo $this->Form->input($this->request->data[$Model][$Field]['description'],array(
	'class' => 'autocomplet',
	'label' => $local,
	'id' => $Model.$Field,
	'name' => 'data['.$Model.']['.$field.']',
	)
);

?>
