<?php
echo '<div class="hint"><p>';
echo $this->Html->link(__('Add Testinginstruction Data'),array_merge(array('action' => 'adddata'),$this->request->projectvars['VarsArray']),array('title' => __('Add Testinginstruction Data'),'class' => 'round modal_post'));
echo '</p></div>';

if(!isset($this->request->data['Devisions']['Results'])) return;
if(count($this->request->data['Devisions']['Results']) == 0) return;

foreach ($this->request->data['Devisions']['Division'] as $key => $value) {

	if(!isset($this->request->data['Devisions']['Results'][$value])) continue;
	if(count($this->request->data['Devisions']['Results'][$value]) == 0) continue;

	echo '<div class="areas" id="' . $this->request->data['Devisions']['Class'][$key] . '">';
	echo '<h3>';
	echo __('Testing Instructions Data',true);
	echo '</h3>';
	echo '<table class="table_resizable table_infinite_sroll"><tr><th> </th>';
	echo '<th>' . __('Field type',true) . '</th>';
	echo '<th>' . __('Field',true) . '</th>';
	echo '<th>' . __('Value',true) . '</th>';
	echo '<th>' . __('Description',true) . '</th>';
	echo '</tr>';

	foreach ($this->request->data['Devisions']['Results'][$value] as $_key => $_value) {

		echo '<tr>';

		$this->request->projectvars['VarsArray'][15] = $_value['testinginstruction_id'];
		$this->request->projectvars['VarsArray'][16] = $_value['id'];

		echo '<td>' . $this->Html->link(__('Edit',true),array_merge(array('action' => 'editdata'),$this->request->projectvars['VarsArray']),array('rel' => $this->request->data['Devisions']['Class'][$key],'class' => 'round modal_post')) . '</td>';
		echo '<td>' . $_value['type'] . '</td>';
		echo '<td>' . $_value['field'] . '</td>';
		echo '<td>' . $_value['value'] . '</td>';
		echo '<td>' . $_value['description'] . '</td>';

		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
}
?>
