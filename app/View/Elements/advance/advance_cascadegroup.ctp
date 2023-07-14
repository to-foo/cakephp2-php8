<?php
$StatusArray[0] = 'deaktiv';
$StatusArray[1] = 'okay';
$StatusArray[2] = 'error';

echo '<div class="hint">';
if(isset($this->request->data['BreadcrumpArray'])) echo $this->element('advance/bread_crump');

$SchemeUrl = $this->Html->url(array_merge(array('action' => 'json_scheme'),$this->request->projectvars['VarsArray']));
$EditUrl = $this->Html->url(array_merge(array('action' => 'advance'),$this->request->projectvars['VarsArray']));
$DelUrl = $this->Html->url(array_merge(array('action' => 'advance_delete'),$this->request->projectvars['VarsArray']));
$AddUrl = $this->Html->url(array_merge(array('action' => 'advance_add'),$this->request->projectvars['VarsArray']));

echo $this->Form->input('SchemeUrl',array('type' => 'hidden','value' => $SchemeUrl));
echo $this->Form->input('EditUrl',array('type' => 'hidden','value' => $EditUrl));
echo $this->Form->input('DelUrl',array('type' => 'hidden','value' => $DelUrl));
echo $this->Form->input('AddUrl',array('type' => 'hidden','value' => $AddUrl));
echo $this->Form->input('CascadeGroupId',array('type' => 'hidden','value' => ''));

echo $this->element('advance/advance_cascadegroup_navigation');
echo $this->element('advance/advance_area_statistic_content');
echo $this->element('advance/advance_cascadegroup_table');
echo '</div>';

?>
