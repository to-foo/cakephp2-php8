<?php
if(!isset($this->request->data[$Model][$Field]['end']) && !isset($this->request->data[$Model][$Field]['start'])) return;

echo $this->Form->input($Model.$Field.'StartTimestamp',array('id' => $Model.$Field.'StartTimestamp','name' => 'data['.$Model.']['.$field.'][start_timestamp]','type' => 'hidden','value' => $this->request->data[$Model][$Field]['start_timestamp']));
echo $this->Form->input($Model.$Field.'EndTimestamp',array('id' => $Model.$Field.'EndTimestamp','name' => 'data['.$Model.']['.$field.'][end_timestamp]','type' => 'hidden','value' => $this->request->data[$Model][$Field]['end_timestamp']));
echo '<div class="input text date">';
echo '<label for="'.$Model.$Field.'Start">'.$this->request->data[$Model][$Field]['description'].'</label>';
echo $this->Form->input($this->request->data[$Model][$Field]['description'] . ' (' . __('from',true) . ')',array(
		'class' => 'date',
		'id' => $Model.$Field.'Start',
		'name' => 'data['.$Model.']['.$field.'][start]',
		'placeholder' => __('from',true),
		'div' => false,
		'label' => false,
	)
);
echo $this->Form->input($this->request->data[$Model][$Field]['description'] . ' (' . __('to',true) . ')',array(
		'class' => 'date',
		'id' => $Model.$Field.'End',
		'name' => 'data['.$Model.']['.$field.'][end]',
		'placeholder' => __('to',true),
		'div' => false,
		'label' => false,
	)
);
echo '</div>';
?>
