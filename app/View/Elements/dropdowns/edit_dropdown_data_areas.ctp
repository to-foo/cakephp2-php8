<?php

$Status = array('0' => __('active',true), '1' => __('deactiv',true));

echo '<div class="areas" id="data_area">';
echo '<h3> ';
echo __('Master dropdown data',true) . ' - ';

if(isset($this->request->data['DropdownsFields'][$this->request->data['DropdownsMaster']['field']])) echo $this->request->data['DropdownsFields'][$this->request->data['DropdownsMaster']['field']];

echo '</h3>';

echo '<div class="hint"><p>';
echo $this->Html->link(__('Add Dropdown Data'),array_merge(array('action' => 'masteradddata'),$this->request->projectvars['VarsArray']),array('title' => __('Add Testinginstruction Data'),'class' => 'round modal_post'));
echo '</p></div>';

if(count($this->request->data['DropdownsMastersData']) == 0) return;

echo '<table class="advancetool">';
echo '<tr>';
echo '<th>' . __('Value',true) . '</th>';
//echo '<th>' . __('Status',true) . '</th>';
echo '</tr>';

$i = 0;
foreach ($this->request->data['DropdownsMastersData']as $_key => $_value) {

	$this->request->projectvars['VarsArray'][16] = $_value['id'];
	$class = null;

	if($i++ % 2 == 0) $class .= ' altrow ';
	if($_value['status'] == 1) $class .= ' deactive ';

	echo '<tr class="' . $class . '">';
	echo '<td class="small_cell">';
	echo $this->Html->link(__('Edit',true),array_merge(array('action' => 'mastereditdata'),$this->request->projectvars['VarsArray']),array('title' => __('Edit',true),'class' => 'icon icon_edit modal_post'));
	if($this->request->data['DropdownsMaster']['dependencies'] == 1) echo $this->Html->link(__('Edit dependency',true),array_merge(array('action' => 'masterdependency'),$this->request->projectvars['VarsArray']),array('title' => __('Edit dependency',true), 'class' => 'icon icon_dependency modal_post'));
	echo $_value['value'];
	echo '</td>';
//	echo '<td>' . $Status[$_value['status']] . '</td>';

	echo '</tr>';

}

echo '</table>';
echo '</div>';

?>
