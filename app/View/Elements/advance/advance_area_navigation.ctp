<?php
$SettingsLInk = $this->request->projectvars['VarsArray'];
$SettingsLInk[1] = $StartId;

echo '<div class="advance_navi">';

echo $this->element('navigation/change_modul_progress');

if(isset($this->request->data['Cascade']) && isset($this->request->data['CascadeGroupId'])){
	echo $this->Html->link(__('Back'),array_merge(array('action' => 'json_scheme'),$SettingsLInk),array('rev' => $this->request->data['Cascade']['id'],'rel' => 'advance_area','title' => __('Back'),'class' => 'round blank'));
}

if(isset($StartId)) {
	echo $this->Form->input('SelectMenue',array(
		'label' => false,
		'div' => false,
		'type' => 'select',
		'selected' => $StartId,
		'options' => $this->request->data['Menue']
		)
	);
}

if(isset($this->request->data['Submenue']) && count($this->request->data['Submenue']) > 0){
	echo $this->Form->input('SelectSubMenue',array(
		'label' => false,
		'div' => false,
		'type' => 'select',
		'options' => $this->request->data['Submenue']
		)
	);

}

echo $this->Html->link(__('Advance settings'),array_merge(array('action' => 'advance_settings'),$SettingsLInk),array('title' => __('Add Advance Data'),'class' => 'round modal'));

echo $this->element('advance/progress_legende');

echo '</div>';
?>
