<?php
if(!isset($this->request->data['DropdownsMastersDependency'])) return;
if(count($this->request->data['DropdownsMastersDependency']) == 0) return;

foreach($this->request->data['DropdownsMastersDependency'] as $key => $value){

	if(count($value) == 0) continue;

	echo '<div class="dependency_table ' . $key . '">';
	echo '<h3>';
	echo __('Dependency values on',true) . ' ';
	echo $this->request->data['DropdownsMaster']['field_name'] . ' > ';
	echo $this->request->data['DropdownsMastersData']['value'] . ' > ';
	echo $this->request->data['DependencyFields'][$key];
	echo '</h3>';
	echo '<table class="advancetool"><tr>';
	echo '<th>' . __('Value',true) . '</th>';
	echo '</tr>';

	$i = 0;

	foreach ($value as $_key => $_value) {

		$this->request->projectvars['VarsArray'][17] = $_value['DropdownsMastersDependency']['id'];
		$class = null;

		if($i++ % 2 == 0) $class .= ' altrow ';

		echo '<tr id="tr_' . $_value['DropdownsMastersDependency']['id'] . '" class="' . $class . '">';
		echo '<td class="value">';
		echo $this->Html->link(__('Edit dependency data'),array_merge(array('action' => 'editdependency'),$this->request->projectvars['VarsArray']),array('json-data' => $_value['DropdownsMastersDependency']['value'],'title' => __('Edit dependency data'),'class' => 'json_edit promt icon icon_edit'));
		echo $this->Html->link(__('Delete dependency data'),array_merge(array('action' => 'deletedependency'),$this->request->projectvars['VarsArray']),array('title' => __('Should this value be deleted') .': ' . $_value['DropdownsMastersDependency']['value'],'class' => 'json_delete confirm icon icon_delete'));
		echo $_value['DropdownsMastersDependency']['value'];
		echo '</td>';
		echo '</tr>';

	}

	echo '</table>';
	echo '</div>';
}
?>
